

postworld.directive( 'pwInfiniteGallery', [ function( $scope ){
	return {
		restrict: 'AE',
		controller: 'pwInfiniteGalleryCtrl',
		link: function( $scope, element, attrs ){

			// Gallery Preload
			attrs.$observe('galleryPreload', function( value ) {
				if( !_.isUndefined( value ) )
					$scope.galleryPreload = parseInt(value);
				else
					$scope.galleryPreload = 3; // Default Preload
			});

		}
	};
}]);


postworld.controller( 'pwInfiniteGalleryCtrl',
	[ '$scope', '$log', '_', '$pw', 'pwData', 'pwPosts',
	function( $scope, $log, $_, $pw, $pwData, $pwPosts ){

	$scope.infiniteGallery = {
		posts:[],		// All the posts from the post gallery object
		displayed:[],	// All the posts which are actually displayed
	};

	///// WATCH : WHEN THE POST CHANGES /////
	$scope.$watchCollection( '[ post.ID, post.gallery ]', function(){
		// IF POST HAS GALLERY
		if( $_.objExists( $scope, 'post.gallery.posts' ) &&
			!_.isEmpty( $_.getObj( $scope, 'post.gallery.posts' ) ) ){

			$scope.infiniteGallery.posts = $scope.post.gallery.posts;

			// Preload Posts
			if( $scope.galleryDisplayedCount() == 0 ){
				$scope.galleryGetNext( $scope.galleryPreload );
			}

		// IF POST HAS NO GALLERY
		} else{
			$scope.infiniteGallery['posts'] = [];
			$scope.infiniteGallery['displayed'] = [];
		}
	}, 1 );

	$scope.galleryPostCount = function(){
		return ( $_.objExists( $scope, 'infiniteGallery.posts.length' ) ) ?
			$scope.infiniteGallery.posts.length : 0;
	}

	$scope.galleryDisplayedCount = function(){
		return $scope.infiniteGallery.displayed.length;
	}

	$scope.galleryPosts = function(){
		return ( $_.objExists( $scope, 'infiniteGallery.posts.length' ) ) ?
			$scope.infiniteGallery.posts : []; 
	}

	$scope.galleryDisplayedPosts = function(){
		return $scope.infiniteGallery.displayed;
	}

	$scope.addDisplayedPosts = function( posts ){
		// Append posts to the displayed posts
		angular.forEach( posts, function( post ){
			$scope.infiniteGallery.displayed.push( post );
		} );
	};

	$scope.galleryGetNext = function( getPostsCount ){

		// Cast Get Posts Count as integer
		getPostsCount = parseInt( getPostsCount );

		// Get the number of total posts
		var galleryPostCount = $scope.galleryPostCount();

		// Get the number of posts already displayed
		var galleryDisplayedCount = $scope.galleryDisplayedCount();

		// Get the Start Index of the slice
		var postsStartIndex = galleryDisplayedCount;

		// Get the End Index of the slice
		var postsEndIndex = postsStartIndex + getPostsCount;

		// Cap the end index at the number of actual items in the array
		if( postsEndIndex >=  galleryPostCount )
			postsEndIndex = galleryPostCount;

		// Slice the posts from the set range
		var addPosts = $scope.galleryPosts().slice( postsStartIndex, postsEndIndex );
		
		// Add them to the array of displayed posts
		$scope.addDisplayedPosts( addPosts );

		// Console
		$log.debug(
			"pwInfiniteGallery.galleryGetNext : " +
			"getPostsCount: " + getPostsCount + " // " + 
			"galleryPostCount: " + galleryPostCount + " // " + 
			"galleryDisplayedCount: " + galleryDisplayedCount + " // " + 
			"postsStartIndex: " + postsStartIndex + " // " + 
			"postsEndIndex: " + postsEndIndex + " // " + 
			"addPosts: ", addPosts
			);
	};

}]);



///// INFINITE HORIZONTAL SCROLL /////
/* BASED ON : ng-infinite-scroll - v1.0.0 - 2013-05-13 */

postworld.directive('infiniteXScroll', [
	'$rootScope', '$window', '$timeout', '$log', function($rootScope, $window, $timeout, $log) {
		return {
			link: function(scope, elem, attrs) {
				var checkWhenEnabled, container, handler, scrollDistance, scrollEnabled;
				$window = angular.element($window);
				scrollDistance = 0;
				if (attrs.scrollDistance != null) {
					scope.$watch(attrs.scrollDistance, function(value) {
						return scrollDistance = parseInt(value, 10);
					});
				}
				scrollEnabled = true;
				checkWhenEnabled = false;
				if (attrs.infiniteScrollDisabled != null) {
					scope.$watch(attrs.infiniteScrollDisabled, function(value) {
						scrollEnabled = !value;
						if (scrollEnabled && checkWhenEnabled) {
							checkWhenEnabled = false;
							return handler();
						}
					});
				}
				container = $window;
				/*
				if (attrs.infiniteScrollContainer != null) {
					scope.$watch(attrs.infiniteScrollContainer, function(value) {
						value = angular.element(value);
						if (value != null) {
							return container = value;
						} else {
							throw new Exception("invalid infinite-scroll-container attribute.");
						}
					});
				}
				*/
				if (attrs.scrollParent != null) {
					container = elem.parent();
					scope.$watch(attrs.scrollParent, function() {
						return container = elem.parent();
					});
				}
				handler = function() {
					var containerBottom, elementBottom, remaining, shouldScroll;
					/*
					///// REMNANTS FROM Y-SCROLL /////
					if (container[0] === $window[0]) {
						containerBottom = container.height() + container.scrollTop();
						elementBottom = elem.offset().top + elem.height();
					} else {
					
					$log.debug(
							'container.scrollTop(): ' + container.scrollTop() + ' / ' +
							'container.scrollLeft(): ' + container.scrollLeft() + ' / ' +
							'container.innerHeight(): ' + container.innerHeight() + ' / ' +
							'container.innerWidth(): ' + container.innerWidth() + ' / ' +
							'container[0].scrollWidth: ' + container[0].scrollWidth + ' / ' +
							'scrollDistance: ' + scrollDistance + ' / ' +
							'remaining: ' + remaining
							//, container
						);
						containerBottom = container.height();
						elementBottom = elem.offset().top - container.offset().top + elem.height();
					}
					*/
					//remaining = elementBottom - containerBottom;
					// Vertical Scrolling
					//shouldScroll =  remaining <= container.height() * scrollDistance;
					// Horizontal Scrolling
					remaining = container[0].scrollWidth - (container.scrollLeft() + container.innerWidth() + scrollDistance);
					shouldScroll =  0 >= remaining;

					/*
						////////// DEV //////////
						$log.debug(
							'container.scrollTop(): ' + container.scrollTop() + ' / ' +
							'container.scrollLeft(): ' + container.scrollLeft() + ' / ' +
							'container.innerHeight(): ' + container.innerHeight() + ' / ' +
							'container.innerWidth(): ' + container.innerWidth() + ' / ' +
							'container[0].scrollWidth: ' + container[0].scrollWidth + ' / ' +
							'scrollDistance: ' + scrollDistance + ' / ' +
							'remaining: ' + remaining
							//, container
						);

						$log.debug(
							'elementBottom: ' + elementBottom + ' / ' +
							'containerBottom: ' + containerBottom
						);

						$log.debug(
							'shouldScroll: ' + shouldScroll + ' / ' +
							'remaining: ' + remaining + ' / ' +
							'container.height(): ' + container.height() + ' / ' +
							'scrollDistance: ' + scrollDistance
						);
					*/

					if (shouldScroll && scrollEnabled) {
						$log.debug("CALL SCROLL ACTION");
						if ($rootScope.$$phase) {
							return scope.$eval(attrs.scrollAction);
						} else {
							return scope.$apply(attrs.scrollAction);
						}
					} else if (shouldScroll) {
						return checkWhenEnabled = true;
					}

				};
				container.on('scroll', handler);
				scope.$on('$destroy', function() {
					return container.off('scroll', handler);
				});
				return $timeout((function() {
					if (attrs.infiniteScrollImmediateCheck) {
						if (scope.$eval(attrs.infiniteScrollImmediateCheck)) {
							return handler();
						}
					} else {
						return handler();
					}
				}), 0);
			}
		};
	}
]);




///// INFINITE VERTICAL SCROLL /////
/* BASED ON : ng-infinite-scroll - v1.0.0 - 2013-05-13 */

postworld.directive('infiniteYScroll', [
	'$rootScope', '$window', '$timeout', '$log', '_', function( $rootScope, $window, $timeout, $log, $_ ) {
		return {
			link: function(scope, elem, attrs) {

				var checkWhenEnabled, container, handler, scrollDistance, scrollEnabled;
				$window = angular.element($window);
				scrollDistance = 0;
				if (attrs.scrollDistance != null) {
					scope.$watch(attrs.scrollDistance, function(value) {
						return scrollDistance = parseInt(value, 10);
					});
				}
				scrollEnabled = true;
				checkWhenEnabled = false;
				if (attrs.scrollDisabled != null) {
					scope.$watch(attrs.scrollDisabled, function(value) {
						scrollEnabled = !value;
						if (scrollEnabled && checkWhenEnabled) {
							checkWhenEnabled = false;
							return handler();
						}
					});
				}

				container = $window;
				$log.debug( "ATTRS.SCROLL CONTAINER", attrs.scrollContainer );
				if ( attrs.scrollContainer != null && typeof attrs.scrollContainer !== 'undefined' ) {
					var value = String( attrs.scrollContainer );
					container = angular.element( attrs.scrollContainer );
					//$log.debug( "<<<<< attrs.scrollContainer : element >>>>> ", container );
					/*
					// Throwing an Error Somehow
					scope.$watch(attrs.scrollContainer, function(value) {
						value = angular.element( String( attrs.scrollContainer ) );
						if ( value != null ) {
							return container = value;
						} else {
							throw new Exception("invalid infinite-scroll-container attribute.");
						}
					});
					*/
				}
				
				if (attrs.scrollParent != null) {
					container = elem.parent();
					scope.$watch(attrs.scrollParent, function() {
						return container = elem.parent();
					});
				}

				handler = function() {
					var containerBottom, elementBottom, remaining, shouldScroll;
					
					if (container[0] === $window[0]) {
						containerBottom = container.height() + container.scrollTop();
						elementBottom = elem.offset().top + elem.height();
					} else {
						containerBottom = container.height();
						elementBottom = elem.offset().top - container.offset().top + elem.height();
					}
					remaining = elementBottom - containerBottom;
					shouldScroll = ( remaining <= scrollDistance );
					
					/*
					////////// DEV //////////
					$log.debug(
						'container.scrollTop(): ' + container.scrollTop() + ' / ' +
						'container.scrollLeft(): ' + container.scrollLeft() + ' / ' +
						'container.innerHeight(): ' + container.innerHeight() + ' / ' +
						'container.innerWidth(): ' + container.innerWidth() + ' / ' +
						'container[0].scrollHeight: ' + container[0].scrollHeight + ' / ' +
						'scrollDistance: ' + scrollDistance + ' / ' +
						'remaining: ' + remaining
						, container
					);
					$log.debug("SCROLLING");
					$log.debug(
						'elementBottom: ' + elementBottom + ' / ' +
						'containerBottom: ' + containerBottom + ' / ' + 
						'remaining : ' + remaining
					);
					$log.debug(
						'shouldScroll: ' + shouldScroll + ' / ' +
						'remaining: ' + remaining + ' / ' +
						'container.height(): ' + container.height() + ' / ' +
						'scrollDistance: ' + scrollDistance
					);
					*/
					
					if (shouldScroll && scrollEnabled) {
						if ($rootScope.$$phase) {
							return scope.$eval(attrs.scrollAction);
						} else {
							return scope.$apply(attrs.scrollAction);
						}
					} else if (shouldScroll) {
						return checkWhenEnabled = true;
					}
				};

				container.on('scroll', handler);
				//$log.debug( 'infinite-y-scroll : container >>>>> ', container );			

				scope.$on('$destroy', function() {
					return container.off('scroll', handler);
				});
				return $timeout((function() {
					if (attrs.scrollImmediateCheck) {
						if (scope.$eval(attrs.scrollImmediateCheck)) {
							return handler();
						}
					} else {
						return handler();
					}
				}), 0);
			}
		};
	}
]);



postworld.directive('infiniteScroll', [
	'$rootScope', '$window', '$timeout', '$log', '_', function($rootScope, $window, $timeout, $log, $_) {
		return {
			link: function(scope, elem, attrs) {
				var checkWhenEnabled, container, handler, scrollDistance, scrollEnabled;
				$window = angular.element($window);
				scrollDistance = 0;
				if (attrs.infiniteScrollDistance != null) {
					scope.$watch(attrs.infiniteScrollDistance, function(value) {
						return scrollDistance = parseInt(value, 10);
					});
				}
				scrollEnabled = true;
				checkWhenEnabled = false;
				if (attrs.infiniteScrollDisabled != null) {
					scope.$watch(attrs.infiniteScrollDisabled, function(value) {
						scrollEnabled = !value;
						if (scrollEnabled && checkWhenEnabled) {
							checkWhenEnabled = false;
							return handler();
						}
					});
				}
				container = $window;
				if (attrs.infiniteScrollContainer != null) {
					scope.$watch(attrs.infiniteScrollContainer, function(value) {
						value = angular.element(value);
						if (value != null) {
							return container = value;
						} else {
							throw new Exception("invalid infinite-scroll-container attribute.");
						}
					});
				}
				if (attrs.infiniteScrollParent != null) {
					container = elem.parent();
					scope.$watch(attrs.infiniteScrollParent, function() {
						return container = elem.parent();
					});
				}
				handler = function() {
					//$log.debug('infinite-scroll handler');
					var containerBottom, elementBottom, remaining, shouldScroll;
					if (container[0] === $window[0]) {
						containerBottom = container.height() + container.scrollTop();
						elementBottom = elem.offset().top + elem.height();
					} else {
						containerBottom = container.height();
						elementBottom = elem.offset().top - container.offset().top + elem.height();
					}
					remaining = elementBottom - containerBottom;
					shouldScroll = remaining <= container.height() * scrollDistance;
					// console.log('elementBottom,containerBottom',elementBottom,containerBottom);
					// console.log('scrolling shouldScroll,remaining, container.height(),scrollDistance',shouldScroll,remaining, container.height(),scrollDistance);
					if (shouldScroll && scrollEnabled) {
						if ($rootScope.$$phase) {
							return scope.$eval(attrs.infiniteScroll);
						} else {
							return scope.$apply(attrs.infiniteScroll);
						}
					} else if (shouldScroll) {
						return checkWhenEnabled = true;
					}

				};
				container.on('scroll', handler );
				scope.$on('$destroy', function() {
					return container.off('scroll', handler);
				});
				return $timeout((function() {
					if (attrs.infiniteScrollImmediateCheck) {
						if (scope.$eval(attrs.infiniteScrollImmediateCheck)) {
							return handler();
						}
					} else {
						return handler();
					}
				}), 0);
			}
		};
	}
]);
