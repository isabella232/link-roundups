<?php

class LRoundupsTestFunctions extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();

		// Set up global $post object
		$this->savedlinks_post = $this->factory->post->create(array('post_type' => 'roundup'));
		global $post;
		$this->tmp_post = $post;
		$post = get_post($this->savedlinks_post);
		setup_postdata($post);
	}

	function tearDown() {
		// Reset global $post object
		$post = $this->tmp_post;
		wp_reset_postdata();
	}

	function test_init() {
		// Testing init would be equivalent to test core WordPress functions called within.
		// There's no need for that kind of testing here.
		$this->markTestSkipped(
			'`LRoundups::init` returns null and uses only core WordPress functions.');
	}

	function test_my_get_posts() {
		$test_query = new WP_Query();
		$test_query->is_home = true;

		LRoundups::my_get_posts($test_query);

		$this->assertTrue(
			in_array('roundup', $test_query->query_vars['post_type']));
	}

	function test_register_post_type() {
		global $wp_post_types;

		LRoundups::register_post_type();

		$this->assertTrue(in_array('roundup', array_keys($wp_post_types)));
	}

	function test_add_custom_post_fields() {
		$this->markTestSkipped(
			'`LRoundups::add_custom_post_fields` returns null and uses only core WordPress functions.');
	}

	function test_display_custom_fields() {
		$this->expectOutputRegex('/argo-links-display-area/');
		LRoundups::display_custom_fields();
	}

	function test_save_custom_fields() {
		$test_url = 'http://testurl';
		$test_des = 'TKTK';

		$_POST['argo_link_url'] = $test_url;
		$_POST['argo_link_description'] = $test_des;

		LRoundups::save_custom_fields($this->savedlinks_post);

		$post_url = get_post_meta($this->savedlinks_post, 'argo_link_url', true);
		$post_des = get_post_meta($this->savedlinks_post, 'argo_link_description', true);

		$this->assertEquals($test_url, $post_url);
		$this->assertEquals($test_des, $post_des);
	}

	function test_add_lroundups_options_page() {
		$this->markTestSkipped(
			'`LRoundups::add_add_lroundups_options_page` returns null and uses only core WordPress functions.');
	}

	function test_register_mysettings() {
		$this->markTestSkipped(
			'`LRoundups::register_mysettings` returns null and uses only core WordPress functions.');
	}

	function test_validate_mailchimp_integration() {
		$ret = LRoundups::validate_mailchimp_integration('test');
		$this->assertEquals($ret, '');
	}

	function test_build_lroundups_options_page() {
		$this->markTestSkipped('This test has not been implemented yet.');
	}
}
