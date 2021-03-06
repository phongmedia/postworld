<?php
// Feeds
$pwFeeds = pw_get_option( array( 'option_name' => PW_OPTIONS_FEEDS ) );
// Feed Settings
$pwFeedSettings = pw_get_option( array( 'option_name' => PW_OPTIONS_FEED_SETTINGS ) );
if( empty($pwFeedSettings) )
	$pwFeedSettings = array( '_' => 0 );

// Feed Templates
$htmlTemplates = pw_get_templates(
	array(
		'subdirs' => array('feeds'),
		'path_type' => 'url',
		'ext'=>'html',
		)
	);
$htmlFeedTemplates = $htmlTemplates['feeds'];
// Aux Feed Templates
$phpTemplates = pw_get_templates(
	array(
		'subdirs' => array('feeds'),
		'path_type' => 'url',
		'ext'=>'php',
		)
	);
$phpFeedTemplates = $phpTemplates['feeds'];

// Contexts to customize feed settings for
$get_contexts = array(
	'default',
	'home',
	'standard',
	'archive',
	'search',
	'taxonomy',
	'post-type'
	);

pw_print_ng_controller(array(
	'app' => 'postworldAdmin',
	'controller' => 'pwFeedsDataCtrl',
	'vars' => array(
		'pwFeeds' => $pwFeeds,
		'pwFeedSettings' => $pwFeedSettings,
		'htmlFeedTemplates' => $htmlFeedTemplates,
		'phpFeedTemplates' => $phpFeedTemplates,
		'contexts' => pw_get_contexts( $get_contexts ),


		),
	));

?>

<?php do_action( 'postworld_admin_header' ) ?>

<div class="postworld feeds wrap">
	<div
		pw-admin
		pw-admin-feeds
		pw-feed-options
		ng-controller="pwFeedsDataCtrl">
		
		<h1 class="primary">
			<i class="icon pwi-th-list"></i>
			<?php _e( 'Feeds', 'postworld' ) ?>
			<button class="add-new-h2" ng-click="newFeed()">
				<?php _e( 'Add New Feed', 'postworld' ) ?>
			</button>
		</h1>

		<hr class="thick">

		<div class="pw-row pw-cloak">

			<!-- ///// ITEMS MENU ///// -->
			<div class="pw-col-3">
				<ul class="list-menu">
					<li
						ng-click="selectItem('settings');"
						ng-class="menuClass('settings')">
						<i class="pwi-gear"></i>
						<?php _e( 'Settings', 'postworld' ) ?>
					</li>
				</ul>
				<hr class="thin">
				<ul class="list-menu">
					<li
						ng-repeat="item in pwFeeds"
						ng-click="selectItem(item)"
						ng-class="menuClass(item)">
						{{ item.name }}
					</li>
				</ul>
				<div class="space-6"></div>
			</div>

			<div class="pw-col-9">
				<!-- ///// EDIT SETTINGS ///// -->
				<div ng-show="showView('settings')" pw-ui ui-views="{}">
					
					<div class="well">
						<!-- SAVE BUTTON -->
						<div class="save-right"><?php pw_save_option_button( PW_OPTIONS_FEED_SETTINGS,'pwFeedSettings'); ?></div>
						<h3>
							<i class="icon pwi-target"></i>
							<?php _e( 'Contexts', 'postworld' ) ?>
						</h3>

						<div class="pw-row well" ng-repeat="context in contexts">
							<div class="pw-col-3">

								<span
									uib-tooltip="{{context.name}}"
									tooltip-popup-delay="333">
									<i class="{{context.icon}}"></i>
									{{context.label}}
								</span>

							</div>
							<div class="pw-col-9">
								<button
									type="button"
									class="button"
									ng-class="uiSetClass('template_'+context.name)"
									ng-click="uiToggleView('template_'+context.name)">
									<i class="pwi-th-large"></i>
									<?php _e( 'Template', 'postworld' ) ?>
								</button>

								<button
									type="button"
									class="button"
									ng-class="uiSetClass('options_'+context.name)"
									ng-click="uiToggleView('options_'+context.name)">
									<i class="pwi-gear"></i>
									<?php _e( 'Options', 'postworld' ) ?>
								</button>

								<div
									ng-show="uiShowView('template_'+context.name)">
									<hr class="thin">
									<?php echo pw_feed_template_options( array( 'ng_model' => 'pwFeedSettings.context[context.name]' ) ); ?>
									<hr class="thin">
									<?php echo pw_feed_variable_options( array( 'ng_model' => 'pwFeedSettings.context[context.name]' ) ); ?>
									
								</div>

								<div
									ng-show="uiShowView('options_'+context.name)">
									<hr class="thin">

									<label>
										<input
											type="number"
											class="short"
											ng-model="pwFeedSettings.context[context.name].preload"
											placeholder="{{pwFeedSettings.context.default.preload}}"
											min="0">
										<b><?php _e( 'Preload', 'postworld' ) ?></b>
										<small>: <?php _e( 'Number of posts to preload on page load', 'postworld' ) ?></small>
									</label>

									<hr class="thin">

									<label>
										<input
											type="number"
											class="short"
											ng-model="pwFeedSettings.context[context.name].load_increment"
											placeholder="{{pwFeedSettings.context.default.load_increment}}"
											min="1">
										<b><?php _e( 'Load Increment', 'postworld' ) ?></b>
										<small>: <?php _e( 'Number of posts to load at a time when infinite scrolling', 'postworld' ) ?></small>
									</label>

								</div>

							</div>

						</div>

					</div>
					
				</div>
				<!-- ///// EDIT ITEMS ///// -->
				<div class="well" ng-show="showView('editItem')">

					<h3>
						<i class="pwi-gear"></i>
						<?php _e( 'Feed Settings', 'postworld' ) ?>
					</h3>

					<div class="pw-row">
						<div class="pw-col-6">
							<label
								for="item-name"
								class="inner"
								uib-tooltip="<?php _e( 'The name is an aesthetic label for the feed.', 'postworld' ) ?>"
								tooltip-popup-delay="333">
								<?php _e( 'Feed Name', 'postworld' ) ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-name"
								class="labeled"
								type="text"
								ng-model="selectedItem.name">
						</div>
						<div class="pw-col-6">
							<label
								for="item-id"
								class="inner"
								uib-tooltip="<?php _e( 'The ID is the unique identifier for the feed. It contains only letters, numbers, and hyphens.', 'postworld' ) ?>"
								tooltip-popup-delay="333">
								<?php _e( 'Feed ID', 'postworld' ) ?>
								<i class="pwi-info-circle"></i>
							</label>
							<button
								class="inner inner-bottom-right inner-controls"
								ng-click="enableInput('#item-id');focusInput('#item-id')"
								uib-tooltip="<?php _e( 'Editing the ID may cause instances of the feed to disappear', 'postworld' ) ?>"
								tooltip-placement="left"
								tooltip-popup-delay="333">
								<i class="pwi-edit"></i>
							</button>
							<input
								id="item-id"
								class="labeled"
								type="text"
								ng-model="selectedItem.id"
								disabled
								pw-sanitize="id"
								ng-blur="disableInput('#item-id');">
						</div>
					</div>

					<div class="pw-row">
						<div class="pw-col-3">
							<label
								for="item-preload"
								class="inner"
								uib-tooltip="<?php _e( 'How many posts to preload', 'postworld' ) ?>"
								tooltip-popup-delay="333">
								<?php _e( 'Preload', 'postworld' ) ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-preload"
								class="labeled"
								type="number"
								ng-model="selectedItem.preload">
						</div>
						<div class="pw-col-3">
							<label
								for="item-load_increment"
								class="inner"
								uib-tooltip="<?php _e( 'How many posts to load each infinite scroll', 'postworld' ) ?>"
								tooltip-popup-delay="333">
								<?php _e( 'Load Increment', 'postworld' ) ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-load_increment"
								class="labeled"
								type="number"
								ng-model="selectedItem.load_increment">
						</div>
						<div class="pw-col-3">
							<label
								for="item-offset"
								class="inner"
								uib-tooltip="<?php _e( 'How many posts to skip at the Javascript level', 'postworld' ) ?>"
								tooltip-popup-delay="333">
								<?php _e( 'Offset', 'postworld' ) ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-offset"
								class="labeled"
								type="number"
								ng-model="selectedItem.offset">
						</div>

					</div>

					<!-- QUERY -->
					<div class="well">
						<h3
							uib-tooltip="{{ selectedItem.query | json }}"
							tooltip-popup-delay="333">
							<i class="pwi-search"></i>
							<?php _e( 'Query', 'postworld' ) ?>
						</h3>
						<?php echo pw_feed_query_options( array( 'ng_model' => 'selectedItem' ) ); ?>
					</div>

					<!-- TEMPLATE -->
					<div class="well">
						<h3>
							<i class="pwi-cube"></i>
							<?php _e( 'Template', 'postworld' ) ?>
						</h3>
						<?php echo pw_feed_template_options( array( 'ng_model' => 'selectedItem' ) ); ?>
						<hr class="thin">
						<?php echo pw_feed_variable_options( array( 'ng_model' => 'selectedItem' ) ); ?>
					</div>

					<!-- SHORTCODE -->
					<div class="well">
						<h3>
							<i class="pwi-code"></i>
							<?php _e( 'Shortcode', 'postworld' ) ?>
						</h3>
						<input
							type="text"
							class="un-disabled"
							style="width:100%;"
							value='[pw-feed id="{{ selectedItem.id }}"]'
							
							select-on-click>
					</div>

					<!-- FEED JSON URL -->
					<?php
						global $wp_version;
						if( $wp_version >= 4.4 ):?>
						<div class="well">
							<h3>
								<i class="pwi-newspaper"></i>
								<?php _e( 'JSON Feed', 'postworld' ) ?>
							</h3>
							<input
								type="text"
								class="un-disabled"
								style="width:100%;"
								value='<?php echo get_site_url() ?>/wp-json/<?php echo pw_rest_namespace() ?>/v1/feed/?id={{ selectedItem.id }}'
								select-on-click>
						</div>
					<?php endif ?>

					<div class="well">

						<!-- SAVE BUTTON -->
						<div class="save-right"><?php pw_save_option_button( PW_OPTIONS_FEEDS,'pwFeeds'); ?></div>
			
						<!-- DELETE BUTTON -->
						<button
							class="button deletion"
							ng-click="deleteItem(selectedItem,'pwFeeds')">
							<i class="pwi-close"></i>
							<?php _e( 'Delete Feed', 'postworld' ) ?>
						</button>

						<!-- DUPLICATE BUTTON -->
						<button
							class="button deletion"
							ng-click="duplicateItem(selectedItem,'pwFeeds')">
							<i class="pwi-copy-2"></i>
							<?php _e( 'Duplicate Feed', 'postworld' ) ?>
						</button>

					</div>

				</div>
			</div>
		</div>

		<?php if( pw_dev_mode() ) : ?>
			<hr class="thick">
			<div class="pw-dev well">
				<h3>
					<i class="pwi-merkaba"></i>
					<?php _e( 'Development Mode', 'postworld' ) ?>
				</h3>
				<div class="well">
					<h3>$scope.pwFeedSettings</h3>
					<pre><code>{{ pwFeedSettings | json }}</code></pre>
				</div>

				<div class="well">
					<h3>$scope.pwFeeds</h3>
					<pre><code>{{ pwFeeds | json }}</code></pre>
				</div>
			</div>

			<?php /*
			<!--
			RADIO BUTTONS
			<b><i class="pwi-calendar"></i> Events Filter</b>
			<br>
			<div class="btn-group">
				<label
					ng-repeat="option in eventOptions.timeFilter"
					class="btn"
					ng-model="eventInput.timeFilter"
					uib-btn-radio="option.value">
					{{ option.name }}
				</label>
			</div>
			-->
			*/ ?>
				
		<?php endif; ?>

	</div>

</div>