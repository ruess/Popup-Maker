<?php
/*******************************************************************************
 * Copyright (c) 2017, WP Popup Maker
 ******************************************************************************/

class PUM_Admin {
	public static function init() {
		PUM_Admin_Assets::init();
		PUM_Admin_Popups::init();
		PUM_Upsell::init();
	}
}