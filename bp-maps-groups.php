<?php
function bp_maps_groups_setup_nav() {
	global $bp;
	
	$group_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $bp->groups->current_group->slug . '/';
	
	bp_core_new_subnav_item( array( 'name' => __( 'Map', 'bp-maps' ), 'slug' => 'map', 'parent_url' => $group_link, 'parent_slug' => $bp->groups->slug, 'screen_function' => 'bp_maps_groups_screen_map', 'item_css_id' => 'map', 'position' => 80, 'user_has_access' => $bp->groups->current_group->user_has_access ) );
}

function bp_maps_groups_screen_map() {
	global $bp;

	if ( $bp->is_single_item )
		bp_core_load_template( apply_filters( 'bp_maps_groups_template_map', 'groups/single/plugins' ) );
}

function bp_is_group_map_page() {
	global $bp;

	if ( BP_GROUPS_SLUG == $bp->current_component && $bp->is_single_item && __('map','bp-maps-slugs') == $bp->current_action )
		return true;

	return false;
}

function bp_maps_group_query($group_id=false) {
	global $wpdb;
	global $bp;
	
	if (!bp_group_has_members( 'exclude_admins_mods=0' ) ) return false;

	while ( bp_group_members() ) : bp_group_the_member();
	
		$users_ids[]= bp_get_group_member_id();
	
	endwhile;
	
	if (!$users_ids) return false;
	
	$ids_str=implode(",",$users_ids);

	///QUERY
	$user_id=$bp->displayed_user->id;
	$type='member_profile';

	//FETCH THE MARKERS WE WANT
	$query = $wpdb->prepare( "SELECT id FROM `{$bp->maps->table_name_markers}` mk WHERE user_id IN ({$ids_str}) AND type='{$type}'");

	$markers_ids = $wpdb->get_col($query );


	$args=array(
		'type'=>'member_profile',
		'showmarkers'=>false,
		'markers_max'=>5,
		'width'=>964, /*max allowed*/
		'height'=>640,
		'display'=>'dynamic'
	);

	//BUILD THE MAP
	return new Bp_Map($args,$markers_ids);
}

function bp_maps_group_has_map($group_id=false) {
	global $bp;
	
	$group_id = $bp->groups->current_group->id;
	
	$map = bp_maps_group_query($group_id);

	return $map;
}




function bp_maps_groups_display_map() {
if ( $map = bp_maps_group_has_map() ) : ?>

	<?php do_action( 'bp_before_group_members_map' ) ?>

	<?php 

	
	bp_maps_map_html($map); ?>
	
	<?php do_action( 'bp_after_group_members_map' ) ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This group has no map.', 'bp-maps-groups' ); ?></p>
	</div>

<?php endif;
}

function bp_maps_groups_init() {

	if (!bp_is_group_map_page()) return false;
	//INIT MAPS JS
	bp_maps_head_init();
	//
	
	add_action( 'bp_template_content', 'bp_maps_groups_display_map' );
	//CHANGE MARKER CONTENT
	add_filter('bp_maps_marker_infobulle_content','bp_maps_profile_marker_infobulle_content',10,2);
}



add_action( 'bp_init','bp_maps_groups_init');
add_action( 'bp_setup_nav', 'bp_maps_groups_setup_nav' );
?>