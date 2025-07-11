<?php

namespace App\Jobs;

use App\Models\Handle;
use App\Models\SocialAccount;
use App\Services\SocialMediaFakerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FindSocialMediaAccount implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Handle $handle)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $finder = new SocialMediaFakerService();
        $socialMediaAccounts = $finder->generateProfiles($this->handle->name);
        $upsertAccounts = [];
        foreach ($socialMediaAccounts as $socialMediaAccount) {
            $upsertAccounts[] = [
                'handle_id' => $this->handle->id,
                'platform' => $socialMediaAccount['platform'],
                'url' => $socialMediaAccount['url'],
                'name' => $socialMediaAccount['name'],
                'bio' => $socialMediaAccount['bio'],
                'profile_image' => $socialMediaAccount['profile_image'],
                'verified' => $socialMediaAccount['verified'],
                'verification_type' => $socialMediaAccount['verification_type'],
                'official_account' => $socialMediaAccount['official_account'],
                'account_type' => $socialMediaAccount['account_type'],
            ];
        }
        SocialAccount::query()->upsert($upsertAccounts, [
            'handle_id',
            'platform',
        ]);
    }
}
