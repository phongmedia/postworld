<?php
/*_____                 _     __  __      _        
 | ____|_   _____ _ __ | |_  |  \/  | ___| |_ __ _ 
 |  _| \ \ / / _ \ '_ \| __| | |\/| |/ _ \ __/ _` |
 | |___ \ V /  __/ | | | |_  | |  | |  __/ || (_| |
 |_____| \_/ \___|_| |_|\__| |_|  |_|\___|\__\__,_|

//////////////////////////////////////////////////*/
global $post;
global $pw_event_post;
?>

<!--///// METABOX WRAPPER /////-->
<div ng-app="pwEventMetabox" id="pwEventMetabox" class="postworld pw-metabox">
	<div ng-controller="pwEventMetaboxCtrl">
		<?php
			// Include the UI template
			$metabox_template = pw_get_template ( 'admin', 'metabox-event', 'php', 'dir' );
			include $metabox_template;
			
			// Action Hook
			do_action('pw_event_metabox_templates');
		?>
		<!-- HIDDEN FIELD -->
		<input type="hidden" name="pw_event_post" ng-value="post | json" style="width:100%;">
		
		<!-- DEV : Test Output -->
		
		<hr><pre>{{ post | json }}</pre>
		
	</div>	
</div>

<!--///// METABOX SCRIPTS /////-->
<script>
	///// APP /////
	var pwEventMetabox = angular.module( 'pwEventMetabox', ['infinite','postworld'] );
	///// CONTROLLER /////
	pwEventMetabox.controller('pwEventMetaboxCtrl',
		['$scope',
			function( $scope ) {
			$scope.post = <?php echo json_encode($pw_event_post); ?>;
			$scope.eventKey = <?php echo json_encode( $event_postmeta_key ); ?>;
	}]);
</script>

<?php
	// Action hook to print the Javascript(s)
	do_action('pw_event_metabox_scripts');
?>

<script>
	///// BOOTSTRAP APP /////
	angular.bootstrap(document.getElementById("pwEventMetabox"),['pwEventMetabox']);
</script>




