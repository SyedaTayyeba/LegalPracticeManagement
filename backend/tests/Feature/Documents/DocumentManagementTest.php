<?php

namespace Tests\Feature\Documents;

use App\Models\CaseFile;
use App\Models\Document;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_lawyer_can_upload_a_document_to_a_case(): void
    {
        Storage::fake('local');

        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $case = CaseFile::factory()->forFirm($firm)->create();
        $token = $this->tokenFor($lawyer);

        $file = UploadedFile::fake()->create('retainer.pdf', 100, 'application/pdf');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/firm/documents', [
                'file' => $file,
                'category' => 'contract',
                'case_id' => $case->uuid,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('documents', ['firm_id' => $firm->id, 'case_id' => $case->id, 'version' => 1]);
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        Storage::fake('local');

        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $file = UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/firm/documents', ['file' => $file, 'category' => 'other']);

        $response->assertStatus(422)->assertJsonPath('error_code', 'DOMAIN_ERROR');
    }

    public function test_new_version_upload_supersedes_the_previous_one(): void
    {
        Storage::fake('local');

        $firm = Firm::factory()->create();
        $lawyer = User::factory()->lawyer()->for($firm)->create();
        $token = $this->tokenFor($lawyer);

        $original = $this->withHeader('Authorization', "Bearer {$token}")->post('/api/v1/firm/documents', [
            'file' => UploadedFile::fake()->create('draft.pdf', 50, 'application/pdf'),
            'category' => 'agreement',
        ])->json('document.id');

        $document = Document::where('uuid', $original)->firstOrFail();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post("/api/v1/firm/documents/{$document->uuid}/versions", [
                'file' => UploadedFile::fake()->create('draft-v2.pdf', 60, 'application/pdf'),
            ]);

        $response->assertCreated()->assertJsonPath('document.version', 2);
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'is_latest_version' => false]);
    }

    public function test_client_can_only_see_documents_flagged_client_visible(): void
    {
        Storage::fake('local');

        $firm = Firm::factory()->create();
        $portalUser = User::factory()->client()->for($firm)->create();
        $client = \App\Models\Client::factory()->for($firm)->create(['user_id' => $portalUser->id]);

        Document::factory()->for($firm)->create(['client_id' => $client->id, 'client_visible' => true, 'name' => 'Visible.pdf']);
        Document::factory()->for($firm)->create(['client_id' => $client->id, 'client_visible' => false, 'name' => 'Hidden.pdf']);

        $token = $this->tokenFor($portalUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/firm/documents');

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Visible.pdf'));
        $this->assertFalse($names->contains('Hidden.pdf'));
    }

    public function test_only_uploader_or_owner_can_delete_a_document(): void
    {
        Storage::fake('local');

        $firm = Firm::factory()->create();
        $uploaderLawyer = User::factory()->lawyer()->for($firm)->create();
        $otherLawyer = User::factory()->lawyer()->for($firm)->create();
        $document = Document::factory()->for($firm)->create(['uploaded_by' => $uploaderLawyer->id]);

        $token = $this->tokenFor($otherLawyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/firm/documents/{$document->uuid}");

        $response->assertStatus(403);
    }
}
