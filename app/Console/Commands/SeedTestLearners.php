<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Membership;
use App\Models\User;
use Database\Seeders\BrandSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedTestLearners extends Command
{
    protected $signature = 'test:seed-learners {--password= : Mật khẩu dùng cho hai tài khoản test}';

    protected $description = 'Tạo hoặc cập nhật hai tài khoản học viên test không có membership';

    public function handle(): int
    {
        $password = (string) ($this->option('password') ?: config('testing.test_learner_password'));
        $brand = app()->bound('brand') ? brand() : $this->dsconsBrand();
        app()->instance('brand', $brand);

        $learners = [
            ['name' => 'Học viên AutoCAD Test', 'email' => 'student.autocad@dscons.test', 'username' => 'student_autocad', 'class' => 'delivery_assassin'],
            ['name' => 'Học viên Navisworks Test', 'email' => 'student.navisworks@dscons.test', 'username' => 'student_navisworks', 'class' => 'continuity_captain'],
        ];

        foreach ($learners as $data) {
            $user = User::withoutGlobalScopes()->firstOrNew(['email' => $data['email']]);
            $user->forceFill([
                ...$data,
                'password' => Hash::make($password),
                'account_type' => 'engineer',
                'level' => 1,
                'xp' => 0,
                'aip' => 0,
                'streak' => 0,
                'source' => 'local-test',
                'is_admin' => false,
                'is_moderator' => false,
                'email_verified_at' => now(),
            ])->save();

            Membership::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('brand_id', $brand->id)
                ->delete();

            // Keep the test accounts as ordinary community participants so
            // they can test posting without accidentally receiving Premium.
            $user->brandRoles()->syncWithoutDetaching([
                $brand->id => ['role' => 'member'],
            ]);

            $this->line($user->email.' | '.$password);
        }

        return self::SUCCESS;
    }

    private function dsconsBrand(): Brand
    {
        $brand = Brand::query()->where('slug', 'dscons')->first();
        if ($brand) {
            return $brand;
        }
        app(BrandSeeder::class)->run();

        return Brand::query()->where('slug', 'dscons')->firstOrFail();
    }
}
