<?php

namespace App\Services;

use Faker\Factory as Faker;

class SocialMediaFakerService
{
    protected $faker;

    protected $platforms = [
        'twitter' => 'https://twitter.com/%s',
        'facebook' => 'https://facebook.com/%s',
        'instagram' => 'https://instagram.com/%s',
        'linkedin' => 'https://linkedin.com/in/%s',
        'youtube' => 'https://youtube.com/%s',
        'tiktok' => 'https://tiktok.com/@%s',
    ];

    protected $verificationRules = [
        'twitter' => ['types' => ['blue', 'gold', 'grey', 'none'], 'weights' => [5, 2, 3, 90], 'official' => ['blue', 'gold']],
        'facebook' => ['types' => ['blue', 'grey', 'none'], 'weights' => [3, 2, 95], 'official' => ['blue']],
        'instagram' => ['types' => ['blue', 'none'], 'weights' => [4, 96], 'official' => ['blue']],
        'linkedin' => ['types' => ['premium', 'none'], 'weights' => [10, 90], 'official' => ['premium']],
        'youtube' => ['types' => ['verified', 'none'], 'weights' => [5, 95], 'official' => ['verified']],
        'tiktok' => ['types' => ['blue', 'none'], 'weights' => [3, 97], 'official' => ['blue']],
    ];

    protected $accountTypes = ['Person', 'Company'];

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    protected function buildWeightedList(array $types, array $weights): array
    {
        $list = [];
        foreach ($types as $i => $type) {
            $count = max(1, (int)round($weights[$i]));
            for ($j = 0; $j < $count; $j++) {
                $list[] = $type;
            }
        }
        return $list;
    }

    public function generateProfiles(string $handle): array
    {
        $accountType = $this->faker->randomElement($this->accountTypes);
        $name = $this->faker->name();
        $bio = $this->faker->sentence(12);
        $profileImage = $this->faker->imageUrl(400, 400, 'people');

        $allKeys = array_keys($this->platforms);
        $pickCount = $this->faker->numberBetween(1, count($allKeys));
        $chosenKeys = $this->faker->randomElements($allKeys, $pickCount);

        $profiles = [];

        foreach ($chosenKeys as $platform) {
            $urlTemplate = $this->platforms[$platform];
            $url = sprintf($urlTemplate, $handle);

            $rule = $this->verificationRules[$platform];
            $weightedBadges = $this->buildWeightedList($rule['types'], $rule['weights']);
            $badge = $this->faker->randomElement($weightedBadges);
            $isVerified = $badge !== 'none';
            $official = $isVerified && in_array($badge, $rule['official'], true);

            $profiles[] = [
                'platform' => ucfirst($platform),
                'handle' => $handle,
                'url' => $url,
                'name' => $name,
                'bio' => $bio,
                'profile_image' => $profileImage,
                'verified' => $isVerified,
                'verification_type' => $isVerified ? $badge : null,
                'official_account' => $official,
                'account_type' => $accountType,
            ];
        }

        return $profiles;
    }
}
