<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedTestRecruiter extends Command
{
    protected $signature = 'test:seed-recruiter {--password= : Mật khẩu cho tài khoản recruiter test}';

    protected $description = 'Tạo hoặc cập nhật recruiter test đã xác minh cho community DSCons';

    public function handle(): int
    {
        $password = (string) ($this->option('password') ?: config('testing.test_recruiter_password'));
        $brand = Brand::query()->where('slug', 'dscons')->firstOrFail();

        $user = User::withoutGlobalScopes()->firstOrNew(['email' => 'recruiter.dscons@dscons.test']);
        $user->forceFill([
            'name' => 'Recruiter DSCons Test',
            'username' => 'recruiter_dscons',
            'password' => Hash::make($password),
            'account_type' => 'recruiter',
            'source' => 'local-test',
            'is_admin' => false,
            'is_moderator' => false,
            'email_verified_at' => now(),
        ])->save();

        $user->brandRoles()->syncWithoutDetaching([$brand->id => ['role' => 'member']]);

        RecruiterProfile::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'user_id' => $user->id],
            [
                'company_name' => 'DSCons Talent Test',
                'company_slug' => 'dscons-talent-test',
                'business_email' => $user->email,
                'industry' => 'BIM/MEP',
                'description' => 'Tài khoản kiểm thử giao diện tuyển dụng DSCons.',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'reviewed_by' => null,
                'review_note' => null,
            ],
        );

        $this->line($user->email.' | '.$password);

        return self::SUCCESS;
    }
}
