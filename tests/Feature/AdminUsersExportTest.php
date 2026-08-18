<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class AdminUsersExportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_export_member_names_and_emails_as_csv(): void
    {
        Carbon::setTestNow('2026-05-22 10:30:00');

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
        ]);
        User::factory()->create([
            'name' => 'Tran Thi B',
            'email' => 'b@example.com',
        ]);

        Auth::login($admin);

        $response = (new AdminUsers())->exportCsv();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('filename=members-20260522-103000.csv', (string) $response->headers->get('content-disposition'));
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));

        $rows = $this->csvRowsFrom($response);

        $this->assertSame(['Họ tên', 'Email'], $rows[0]);
        $this->assertContains(['Nguyen Van A', 'a@example.com'], $rows);
        $this->assertContains(['Tran Thi B', 'b@example.com'], $rows);
    }

    public function test_non_admin_does_not_get_member_csv_export(): void
    {
        Auth::login(User::factory()->create(['is_admin' => false]));

        $this->assertNull((new AdminUsers())->exportCsv());
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function csvRowsFrom(StreamedResponse $response): array
    {
        ob_start();
        $response->sendContent();
        $content = (string) ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $content = substr($content, 3);

        return collect(preg_split('/\r\n|\n|\r/', trim($content)))
            ->filter(fn($line) => $line !== '')
            ->map(fn($line) => str_getcsv($line))
            ->values()
            ->all();
    }
}
