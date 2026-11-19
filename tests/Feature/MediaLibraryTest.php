<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_editor_can_upload_search_and_update_image_metadata(): void
    {
        Storage::fake('public');
        $editor = User::factory()->create(['role' => 'content_editor']);

        $this->actingAs($editor)->post(route('admin.media.store'), [
            'category' => 'home', 'files' => [UploadedFile::fake()->image('sunlit-room.jpg', 320, 240)],
        ])->assertRedirect();

        $asset = MediaAsset::firstOrFail();
        $this->assertStringEndsWith('.webp', $asset->path);
        Storage::disk('public')->assertExists($asset->path);
        $this->actingAs($editor)->get(route('admin.media', ['search' => 'sunlit']))->assertOk()->assertSee('sunlit-room.jpg');
        $this->actingAs($editor)->patch(route('admin.media.update', $asset), [
            'title' => 'Sunlit living room', 'alt_text' => 'A bright room with a sofa and plant.', 'category' => 'home',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('media_assets', ['id' => $asset->id, 'title' => 'Sunlit living room']);
    }

    public function test_replacing_media_updates_existing_content_references_and_deletion_is_blocked_while_in_use(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $path = UploadedFile::fake()->image('original.png', 240, 180)->store('media', 'public');
        $replacement = UploadedFile::fake()->image('replacement.png', 240, 180);
        $asset = MediaAsset::create([
            'path' => $path, 'original_name' => 'original.png', 'title' => 'Original', 'alt_text' => 'Original image',
            'category' => 'home', 'mime_type' => 'image/png', 'size' => 1024,
        ]);
        $service = Service::create(['name' => 'Moving', 'slug' => 'moving-services', 'summary' => 'Moves.', 'image' => 'storage/'.$path]);
        $gallery = GalleryItem::create(['path' => $path, 'alt_text' => 'Moving image', 'category' => 'Moving']);
        $page = ContentPage::create(['slug' => 'welcome', 'title' => 'Welcome', 'summary' => 'Welcome page', 'body' => '## Hello', 'image' => 'storage/'.$path]);
        SiteSetting::create(['key' => 'cta_image', 'value' => 'storage/'.$path]);

        $this->actingAs($admin)->delete(route('admin.media.delete', $asset))->assertSessionHasErrors('media');
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin)->patch(route('admin.media.update', $asset), [
            'title' => 'Replacement', 'alt_text' => 'Updated room image', 'category' => 'home', 'replacement' => $replacement,
        ])->assertSessionHasNoErrors();

        $asset->refresh();
        $service->refresh();
        $gallery->refresh();
        $page->refresh();
        $this->assertNotSame($path, $asset->path);
        $this->assertSame('storage/'.$asset->path, $service->image);
        $this->assertSame($asset->path, $gallery->path);
        $this->assertSame('storage/'.$asset->path, $page->image);
        $this->assertDatabaseHas('site_settings', ['key' => 'cta_image', 'value' => 'storage/'.$asset->path]);
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertExists($asset->path);
    }
}
