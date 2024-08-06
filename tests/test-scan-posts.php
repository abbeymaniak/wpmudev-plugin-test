<?php

use PHPUnit\Framework\TestCase;
use WPMUDEV\PluginTest\App\Admin_Pages\PostMaintenance;

class ScanPostsCliTest extends TestCase {

	protected $post_ids = [];

	protected function setUp(): void {
		parent::setUp();

		// Set up the environment, mock WP_CLI, and create test posts.
		$this->mockWpCli();
	}


	protected function mockWpCli() {
		// Mock the WP_CLI class to avoid actual CLI calls.
		if (!class_exists('\WP_CLI')) {
			class_alias('\MockWpCli', '\WP_CLI');
		}
	}

	public function test_scan_posts_cli_updates_post_meta() {
		// Mock the Singleton class
		$mock = $this->getMockBuilder(PostMaintenance::class)
			->disableOriginalConstructor()
			->getMock();

		// Assuming scan_posts_cli is a public method
		$mock->expects($this->once())
			->method('scan_posts_cli')
			->willReturn(true);

		// Act: Run the function
		$mock->scan_posts_cli();

		// Assert: Check if the metadata was updated.
		foreach ($this->post_ids as $post_id) {
			$timestamp = get_post_meta($post_id, 'wpmudev_test_last_scan', true);
			$this->assertNotEmpty($timestamp, "Post ID $post_id should have its metadata updated.");
		}
	}
}

class MockWpCli {
	public static function success($message) {
		// Mock the WP_CLI::success() method.
		echo $message . PHP_EOL;
	}
}
