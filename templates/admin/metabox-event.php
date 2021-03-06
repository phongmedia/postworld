<div
	pw-event-input="post.post_meta[ eventKey ]">

	<div class="pw-row">
		<div class="pw-col-6">

			<h3>Event Start</h3>

			<!-- DATE DROPDOWN -->
			<span uib-dropdown class="time dropdown">
				<button uib-dropdown-toggle class="button">
					<i class="pwi-calendar"></i>
					{{ post.post_meta[ eventKey ].date.start_date_obj | date:'MMMM dd, yyyy' }}
				</button>
				<ul
					class="dropdown-menu pull-left" 
					stop-propagation-click>
					<div class="well well-small pull-left">
						<datepicker
							ng-model="post.post_meta[ eventKey ].date.start_date_obj"
							show-weeks="false">
						</datepicker>
					</div>
				</ul>
			</span>

			<!-- TIME DROPDOWN -->
			<span
				uib-dropdown
				class="time dropdown"
				ng-hide="post.post_meta[ eventKey ].date.all_day">
				<button uib-dropdown-toggle class="button">
					<i class="pwi-clock"></i>
					{{ post.post_meta[ eventKey ].date.start_date_obj | date:'shortTime' }}
				</button>
				<ul class="dropdown-menu pull-left" stop-propagation-click>
					<div
						ng-model="post.post_meta[ eventKey ].date.start_date_obj"
						class="well well-small"
						style="display:inline-block;">
						<timepicker
							hour-step="1"
							minute-step="1"
							show-meridian="ismeridian">
						</timepicker>
						<button
							class="button"
							ng-click="ismeridian = !ismeridian">
							12H / 24H
						</button>
					</div>
				</ul>
			</span>

		</div>
		<div class="pw-col-6">
			
			<h3>Event End</h3>

			<!-- DATE DROPDOWN -->
			<span uib-dropdown class="time dropdown">
				<button uib-dropdown-toggle class="button">
					<i class="pwi-calendar"></i>
					{{ post.post_meta[ eventKey ].date.end_date_obj | date:'MMMM dd, yyyy' }}
				</button>
				<ul class="dropdown-menu pull-left" stop-propagation-click>
					<div class="well well-small pull-left">
						<datepicker
							ng-model="post.post_meta[ eventKey ].date.end_date_obj"
							show-weeks="false">
						</datepicker>
					</div>
				</ul>
			</span>

			<!-- TIME DROPDOWN -->
			<span
				uib-dropdown
				class="time dropdown"
				ng-hide="post.post_meta[ eventKey ].date.all_day">
				<button uib-dropdown-toggle class="button">
					<i class="pwi-clock"></i>
					{{ post.post_meta[ eventKey ].date.end_date_obj | date:'shortTime' }}
				</button>
				<ul class="dropdown-menu pull-left" stop-propagation-click>
					<div
						ng-model="post.post_meta[ eventKey ].date.end_date_obj"
						class="well well-small"
						style="display:inline-block;">
						<timepicker
							hour-step="1"
							minute-step="1"
							show-meridian="ismeridian">
						</timepicker>
						<button
							class="button"
							ng-click="ismeridian = !ismeridian">
							12H / 24H
						</button>
					</div>
				</ul>
			</span>
		</div>
	</div>


	<div class="event-all-day">
		<hr class="thin">
		<label>
			<input
				type="checkbox"
				ng-model="post.post_meta[ eventKey ].date.all_day">
				All Day Event
		</label>
	</div>

</div>

<hr>


<!-- LOCATION -->
<div
	pw-geo-autocomplete
	
	pw-geo-input
	geo-post="post"
	geo-location-obj="post.post_meta[ eventKey ].location"
	geo-return-obj=""
	class="input_module labeled">

		<!-- TODO :
		<div ng-show="uiShowView('mapUnit')">
			<google-map
				id="googleMap"
				center='map.center'
				zoom='map.zoom'
				draggable='map.draggable'
				options='map.options'
				events="map.events">
				<marker
					coords='marker'
					options='marker.options'
					click="clickMarker()">
				</marker>
			</google-map>
		</div>
	-->
</div>

<div class="pw-row row-primary">
	<!-- LEFT COLUMN -->
	<div class="pw-col-6">
		<h3>Location</h3>

		<div
			pw-ui
			ui-views="{ searchInput: false }">
			
			<!-- UI VIEWS : <pre>{{ uiViews | json }}</pre><hr> -->
			<!-- SEARCH BUTTON -->
			<div
				ng-show="!uiShowView('searchInput')">
				<button
					id="searchLocations"
					class="button float-left"
					name="search"
					type="button"
					ng-click="uiToggleView('searchInput'); uiFocusElement('#searchBarInput')">
					<i class="pwi-search"></i> Search Locations
				</button>
				<h4 class="float-left unit">
					<i>Autocomplete Geocode</i>
				</h4>
				<div class="clearfix"></div>
			</div>
			
			<!-- SEARCH INPUT -->
			<div
				ng-show="uiShowView('searchInput')"
				style="position:relative;">
				<label for="location" class="inner inner-right unit transparent">
					<i ng-show="loadingLocations" class="pwi-spinner-2 icon-spin"></i>
				</label>
				<label for="location" class="input-icon">
					<i class="pwi-search"></i>
				</label>
				<input
					id="searchBarInput"
					type="text"
					ng-model="post.post_meta[ eventKey ].location.geocode"
					placeholder="Type Location..."
					uib-typeahead="address.formatted_address as address.formatted_address for address in getLocation($viewValue)"
					typeahead-loading="loadingLocations"
					typeahead-on-select="addGeocode($item);"
					placeholder=""
					ng-blur="uiToggleView('searchInput');"
					class="input-icon-left"
					style="width:100%;">
				<!--<button
					class="button"
					type="button"
					ng-click="toggleView('searchInput');">
					<i class="pwi-close"></i>
				</button>-->
			</div>
		</div>

		<hr class="thin">

		<div
			class="well"
			ng-show="uiBool( post.post_meta[ eventKey ].location.formatted_address )">
			<label for="formatted_address" class="input-icon">
				<i class="icon pwi-location"></i>
			</label>
			<input
				id="formatted_address"
				type="text"
				class="input-icon-left"
				style="width:100%;"
				ng-model="post.post_meta[ eventKey ].location.formatted_address">
		</div>

		<div class="pw-row">
			<div class="pw-col-12">
				<label class="inner">Name</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.name">
			</div>
		</div>
		<div class="pw-row">
			<div class="pw-col-12">
				<label class="inner">Address</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.address">
			</div>
		</div>
		<div class="pw-row">
			<div class="pw-col-6">
				<label class="inner">City</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.city">
			</div>
			<div class="pw-col-6">
				<label class="inner">State / Province</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.region">
			</div>
		</div>
		<div class="pw-row">
			<div class="pw-col-6">
				<label class="inner">Country</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.country">
			</div>
			<div class="pw-col-6">
				<label class="inner">Postal Code</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].location.postal_code">
			</div>
		</div>
		<hr class="thin">
		<div class="pw-row">
			<div class="pw-col-6">
				<label class="inner">Latitude</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.geo_latitude"
					placeholder="0.000">
			</div>
			<div class="pw-col-6">
				<label class="inner">Longitude</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.geo_longitude"
					placeholder="0.000">
			</div>
		</div>

		<div
			pw-timezone
			timezone-latitude="post.geo_latitude"
			timezone-longitude="post.geo_longitude"
			timezone-obj="post.post_meta[ eventKey ].timezone"
			>

			<hr class="thin">

			<div
				pw-event="post.post_meta[ eventKey ]"
				event-obj="eventData">
			
				<div ng-show="uiBool(post.post_meta[ eventKey ].timezone.time_zone_name)">		

					<h3>Event Timezone</h3>
					<button
						type="button"
						class="button"
						disabled>
						<i class="pwi-clock"></i>
						{{ eventData.eventTimezone.name }}
						//
						{{ eventData.eventTimezone.code }}
						( UTC {{ eventData.eventTimezone.offset / 60 }}h )
					</button>
					<button
						type="button"
						class="button"
						ng-click="removeTimeZone()">
						<i class="icon pwi-close"></i> Remove Timezone
					</button>

					<hr class="thin">

				</div>
				
				<h3>Client Timezone</h3>
				<button
					type="button"
					class="button"
					disabled>
					<i class="pwi-clock"></i>
					{{ eventData.clientTimezone.name }}
					//
					{{ eventData.clientTimezone.code }}
					( UTC {{ eventData.clientTimezone.offset / 60 }}h )
				</button>

			</div>
			
		</div>

	</div>

	<!-- RIGHT COLUMN -->
	<div class="pw-col-6">
		<h3>Organizer</h3>
		<div class="pw-row">
			<div class="pw-col-12">
				<label class="inner">Name</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].organizer.name">
			</div>
		</div>

		<div class="pw-row">
			<div class="pw-col-6">
				<label class="inner">Phone</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].organizer.phone">
			</div>
			<div class="pw-col-6">
				<label class="inner">Email</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].organizer.email">
			</div>
		</div>
		<div class="pw-row">
			<div class="pw-col-12">
				<label class="inner">Social Profile Link</label>
				<input
					type="url"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].organizer.link_url"
					placeholder="http://">
			</div>
		</div>


		<h3>Tickets</h3>
		<div class="pw-row">
			<div class="pw-col-4">	
				<label class="inner">Cost</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].details.tickets_cost">
			</div>
			<div class="pw-col-8">
				<label class="inner">Link to Buy Tickets</label>
				<input
					type="url"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].details.tickets_url"
					placeholder="http://">
			</div>
		</div>

		<h3>Additional Link</h3>
		<div class="pw-row">
			<div class="pw-col-4">	
				<label class="inner">Label</label>
				<input
					type="text"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].details.link_label">
			</div>
			<div class="pw-col-8">
				<label class="inner">Link to Offsite Event Page</label>
				<input
					type="url"
					class="labeled"
					ng-model="post.post_meta[ eventKey ].details.link_url"
					placeholder="http://">
			</div>
		</div>

	</div>
</div>

<?php if( pw_dev_mode() ): ?>
	<div class="well">
		<h3>pw-event</h3>
		<div pw-event="post.post_meta.pw_event" event-obj="myEvent">
			<pre><code>{{ myEvent | json }}</code></pre>

			<!-- MAKE COUNTDOWN OBJECT -->
		
		</div>
	</div>
<?php endif; ?>
