<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ExtractWordsFromImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        // Arrange - не вызываем actingAs

        // Act
        $response = $this->get(route('words.extract-from-image.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_validates_required_image(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->post(route('words.extract-from-image.extract'), [
            'language' => 'en',
        ]);

        // Assert
        $response->assertSessionHasErrors(['image']);
    }

    public function test_validates_required_language(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->post(route('words.extract-from-image.extract'), [
            'image' => $image,
        ]);

        // Assert
        $response->assertSessionHasErrors(['language']);
    }

    public function test_validates_language_size(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->post(route('words.extract-from-image.extract'), [
            'image' => $image,
            'language' => 'eng',
        ]);

        // Assert
        $response->assertSessionHasErrors(['language']);
    }

    public function test_validates_image_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1024);

        // Act
        $response = $this->post(route('words.extract-from-image.extract'), [
            'image' => $file,
            'language' => 'en',
        ]);

        // Assert
        $response->assertSessionHasErrors(['image']);
    }

    public function test_validates_image_size(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // 11 MB - превышает лимит в 10MB
        $image = UploadedFile::fake()->create('test.jpg', 11264);

        // Act
        $response = $this->post(route('words.extract-from-image.extract'), [
            'image' => $image,
            'language' => 'en',
        ]);

        // Assert
        $response->assertSessionHasErrors(['image']);
    }
}
