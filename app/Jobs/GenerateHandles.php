<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Handle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\StringSchema;

class GenerateHandles implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Company $company)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $companyJson = Company::query()->with([
            'companySubsidiaries',
            'industries',
            'aliases',
            'categories',
            'brands',
            'products',
        ])->where('id', $this->company->id)->first()->toJson();

        $systemPrompt = <<<PROMPT
You are an expert in social media handle generation, with deep understanding of naming conventions, brand architecture, and digital behavior.
Your job is to create the largest and most exhaustive list of possible social media handles for a company, aiming for thousands of intelligently generated possibilities.
Based on detailed input - including company name, subsidiaries, industries, aliases, categories, brands, products, and regions.
You must think creatively and strategically to construct every conceivable handle pattern. Use TitleCase for handles.
Use deep combinatorial logic to mix and match full names, abbreviations, acronyms, initials, keywords, product lines, industries, related industry keywords, and regional or functional modifiers.
Go beyond surface-level ideas by simulating the naming behavior of companies, marketers, and fans alike. Reorder terms logically.
After your handles list is prepared, Generate more comprehensive list by using different types of casing.
Output must be in structured JSON format, an array of intelligently generated handles. Group it with company key.
PROMPT;

        $schema = new ArraySchema(
            name: 'handles',
            description: 'List of social media handles',
            items: new StringSchema('handle', 'A social media handle string')
        );

        $response = Prism::structured()
            ->using(Provider::Gemini, 'gemini-2.5-pro')
            ->withClientOptions(['timeout' => '3600'])
            ->withSchema($schema)
            ->withMaxTokens(100000)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($companyJson)
            ->asStructured()->structured;

        $this->storeHandles($response);

        foreach (Handle::query()->where('company_id', $this->company->id)->get() as $handle) {
            FindSocialMediaAccount::dispatchSync($handle);
        }

        $this->company->status = 'finished';
        $this->company->save();
    }

    private function storeHandles($handles): void
    {
        $upsert = [];

        foreach ($handles as $handle) {
            $upsert[] = [
                'company_id' => $this->company->id,
                'name' => $handle,
            ];
        }

        Handle::query()->upsert($upsert, ['name']);
    }
}
