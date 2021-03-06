<?php
/*_____             _  __        ___     _            _
 |  ___|__  ___  __| | \ \      / (_) __| | __ _  ___| |_
 | |_ / _ \/ _ \/ _` |  \ \ /\ / /| |/ _` |/ _` |/ _ \ __|
 |  _|  __/  __/ (_| |   \ V  V / | | (_| | (_| |  __/ |_
 |_|  \___|\___|\__,_|    \_/\_/  |_|\__,_|\__, |\___|\__|
										   |___/
////////////////// FEED WIDGET - ADMIN //////////////////*/
?>
<?php

if ( isset( $OPTIONS[ 'title' ] ) ) {
	  $title = $OPTIONS[ 'title' ];
	}
	else {
	  $title = __( 'Widget', 'text_domain' );
	}
extract($OPTIONS);
?>

<style>
	#widget-options .type-wrapper {
		border: 1px solid #ccc;
		border-radius: 4px;
		margin-top: 5px;
		padding: 10px;
	}
	#widget-options .radio-type-select { margin-right: 5px; }
	#widget-options .type-title { font-size: 14px; letter-spacing: 1px; color: #666; }
	#widget-options .type-options { margin: 10px 0 0 10px; }
</style>
<div class="postworld">
	<div id="widget-options" class="postworld-widget">

		<!-- TITLE -->
		<label class="inner" for="<?php echo $this->get_field_id( 'show_title' ); ?>">
			<input
				type="checkbox"
				value="1"
				title="Show Title"
				name="<?php echo $this->get_field_name('show_title'); ?>"
				id="<?php echo $this->get_field_id('show_title'); ?>"
				<?php if( !empty($show_title) && $show_title == '1' ){ echo 'checked="checked"'; } ?> >
			<?php _e( 'title', 'postworld' ); ?>
		</label>
		<input
			class="labeled"
			id="<?php echo $this->get_field_id( 'title' ); ?>"
			name="<?php echo $this->get_field_name( 'title' ); ?>"
			type="text" value="<?php echo esc_attr( $title ); ?>" />
		<hr class="thin">

		<label class="inner">
			Feed
		</label>
		<select
			class="labeled"
			name="<?php echo $this->get_field_name('feed_id'); ?>"
			id="<?php echo $this->get_field_id('feed_id'); ?>">
			<?php

				$pw_feeds = pw_get_option( array( 'option_name'  =>  PW_OPTIONS_FEEDS ) );

				if( !empty( $pw_feeds ) ){
					foreach($pw_feeds as $feed ) {
						$selected = '';
						$selected = ( $feed_id == $feed['id'] ) ? 'selected="selected"' : '';
						echo '<option value="'.$feed['id'].'" '.$selected.' >'.$feed['name'].'</option>';
					}
				}

			?>
		</select>
		

	</div>
</div>