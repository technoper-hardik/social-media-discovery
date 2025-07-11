<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class ExtractCompanyDetails implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Company $company, protected bool $fetchSubsidiaries = false)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = $this->fetchCompanyDetails();

        $this->storeDetails($this->company, $response);

        if ($this->fetchSubsidiaries) {
            foreach ($this->company->subsidiaries()->get() as $company) {
                ExtractCompanyDetails::dispatchSync($company);
            }
        }

        if ($this->fetchSubsidiaries) {
            GenerateHandles::dispatchSync($this->company);
        }
    }

    private function storeDetails(Company $company, array $response): void
    {
        $company->description = $response['description'];
        $company->save();

        foreach ($response['industries'] as $industry) {
            $company->industries()->updateOrCreate([
                'name' => $industry
            ]);
        }

        foreach ($response['subsidiaries'] as $subsidiary) {
            Company::query()->updateOrCreate([
                'name' => $subsidiary['name'],
            ], [
                'parent_id' => $this->company->id,
                'website' => $subsidiary['website'],
            ]);
        }

        foreach ($response['aliases'] as $aliasName) {
            CompanyAlias::query()->updateOrCreate([
                'company_id' => $company->id,
                'name' => $aliasName,
            ]);
        }

        $brands = [];

        foreach ($response['brands'] as $brand) {
            $brand = Brand::query()->updateOrCreate([
                'company_id' => $company->id,
                'name' => $brand['name'],
            ], [
                'description' => $brand['description'],
                'website' => $brand['website'],
            ]);

            $brands[$brand->name] = $brand->id;
        }

        foreach ($response['products'] as $product) {
            Product::query()->updateOrCreate([
                'company_id' => $company->id,
                'name' => $product['name'],
            ], [
                'brand_id' => $brands[$product['brand']] ?? null,
                'description' => $product['description'],
                'website' => $product['website']
            ]);
        }

        foreach ($response['products'] as $product) {
            $company->categories()->updateOrCreate([
                'name' => $product['category'],
            ]);
        }
    }

    private function fetchCompanyDetails(): ?array
    {
        $prompt = <<<PROMPT
Provide me detailed information about {$this->company->name} (Website {$this->company->website})
including all Company Name Aliases, subsidiaries (Only Companies), Brands, Industries ({$this->company->name} works in),
Products and It's category, and detailed description about the company.
Don't include regional variation of subsidiaries, brands, and products.
PROMPT;

        return Prism::structured()
            ->using(Provider::OpenAI, 'gpt-4.1-2025-04-14')
            ->withSchema($this->schema())
            ->withClientOptions(['timeout' => '3600'])
            ->withMaxTokens(100000)
            ->withSystemPrompt('You are researcher, You will going to do deep research on internet and extract precise information. You have to create perfect json output it is kind of like big project and you will not going to miss single thing.')
            ->withPrompt($prompt)
            ->asStructured()->structured;
    }

    private function schema(): ObjectSchema
    {
        $brandSchema = new ObjectSchema(
            name: 'brand_info',
            description: 'A structured schema representing detailed information about the company\'s brands',
            properties: [
                new StringSchema('name', 'Name of brand'),
                new StringSchema('description', 'Detailed description of the brand'),
                new StringSchema('website', 'A website of the brand'),
            ]
        );

        $productSchema = new ObjectSchema(
            name: 'product_info',
            description: 'A structured schema representing detailed information about the company\'s products',
            properties: [
                new StringSchema('name', 'Name of product'),
                new StringSchema('description', 'Detailed description of the product'),
                new StringSchema('website', 'A website of the product'),
                new StringSchema('brand', 'Name of the product\'s brand'),
                new StringSchema('category', 'Name of the product\'s category'),
            ]
        );

        $subsidiarySchema = new ObjectSchema(
            name: 'subsidiary_info',
            description: 'A structured schema representing detailed information about the company\'s subsidiary',
            properties: [
                new StringSchema('name', 'Name of subsidiary'),
                new StringSchema('website', 'A website of the subsidiary'),
            ]
        );

        return new ObjectSchema(
            name: 'company_info',
            description: 'A structured schema representing detailed information about Corporation',
            properties: [
                new StringSchema('name', 'Company name'),
                new StringSchema('description', 'Detailed description of the company'),
                new ArraySchema('aliases', 'List of company aliases', new StringSchema('industry', 'Alias Name of company')),
                new ArraySchema('industries', 'List of industries operates in', new StringSchema('industry', 'Name of industry')),
                new ArraySchema('subsidiaries', 'List of subsidiaries', $subsidiarySchema),
                new ArraySchema('brands', 'List of brands', $brandSchema),
                new ArraySchema('products', 'List of products in the category', $productSchema)
            ],
            requiredFields: ['name', 'description', 'aliases', 'industries', 'subsidiaries', 'brands', 'products']
        );
    }
}
