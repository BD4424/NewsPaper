<?php
/**
 * Live Updates: tick "Live Updates post" in News details, then add timestamped entries
 * from the "Live updates" box. Newest on top; readers' pages refresh every 60s (REST).
 * Meta np_live_entries = [ [ 'id' => string, 'time' => int (UTC), 'text' => html ], ... ]
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_core_live_entries( int $post_id ): array {
	$e = get_post_meta( $post_id, 'np_live_entries', true );
	$e = is_array( $e ) ? $e : array();
	usort( $e, fn( $a, $b ) => $b['time'] <=> $a['time'] );
	return $e;
}

function np_core_live_entry_html( array $e ): string {
	$t = function_exists( 'np_odia_date' ) ? np_odia_date( (int) $e['time'], true ) : wp_date( 'j M Y, g:i A', (int) $e['time'] );
	return '<li class="np-live__entry" data-time="' . esc_attr( (string) $e['time'] ) . '"><time datetime="' . esc_attr( gmdate( 'c', (int) $e['time'] ) ) . '">' . esc_html( $t ) . '</time><div>' . wp_kses_post( wpautop( $e['text'] ) ) . '</div></li>';
}

/* ---------- Admin box ---------- */
add_action( 'add_meta_boxes_post', function ( WP_Post $p ) {
	if ( ! get_post_meta( $p->ID, 'np_live', true ) ) { return; }
	add_meta_box( 'np-live', __( 'Live updates', 'newspaper-core' ), function ( WP_Post $p ) {
		wp_nonce_field( 'np_live_' . $p->ID, 'np_live_nonce' ); ?>
		<p><textarea id="np-live-text" class="widefat" rows="3" placeholder="<?php esc_attr_e( 'Type the update and press "Post update"…', 'newspaper-core' ); ?>"></textarea></p>
		<p><button type="button" class="button button-primary" id="np-live-add" data-post="<?php echo esc_attr( (string) $p->ID ); ?>"><?php esc_html_e( 'Post update', 'newspaper-core' ); ?></button></p>
		<ul id="np-live-list">
			<?php foreach ( np_core_live_entries( $p->ID ) as $e ) : ?>
				<li data-id="<?php echo esc_attr( $e['id'] ); ?>"><strong><?php echo esc_html( wp_date( 'j M, g:i A', (int) $e['time'] ) ); ?></strong> — <?php echo esc_html( wp_trim_words( wp_strip_all_tags( $e['text'] ), 20 ) ); ?> <button type="button" class="button-link-delete np-live-del"><?php esc_html_e( 'Delete', 'newspaper-core' ); ?></button></li>
			<?php endforeach; ?>
		</ul>
		<script>
		(function(){
			var add=document.getElementById('np-live-add'),txt=document.getElementById('np-live-text'),list=document.getElementById('np-live-list'),nonce=document.getElementById('np_live_nonce').value;
			function call(data){data.append('nonce',nonce);data.append('post',add.dataset.post);return fetch(ajaxurl,{method:'POST',body:data,credentials:'same-origin'}).then(function(r){return r.json();});}
			add.addEventListener('click',function(){
				if(!txt.value.trim())return;var d=new FormData();d.append('action','np_live_add');d.append('text',txt.value);add.disabled=true;
				call(d).then(function(r){add.disabled=false;if(r.success){list.insertAdjacentHTML('afterbegin',r.data.row);txt.value='';}else{alert(r.data||'Error');}});
			});
			list.addEventListener('click',function(e){
				if(!e.target.classList.contains('np-live-del')||!confirm('Delete this update?'))return;var li=e.target.closest('li'),d=new FormData();d.append('action','np_live_del');d.append('id',li.dataset.id);
				call(d).then(function(r){if(r.success)li.remove();});
			});
		})();
		</script>
		<?php
	}, 'post', 'normal', 'high' );
} );

function np_core_live_check(): int {
	$post = absint( $_POST['post'] ?? 0 );
	if ( ! $post || ! check_ajax_referer( 'np_live_' . $post, 'nonce', false ) || ! current_user_can( 'edit_post', $post ) ) { wp_send_json_error( __( 'Not allowed', 'newspaper-core' ), 403 ); }
	return $post;
}

add_action( 'wp_ajax_np_live_add', function () {
	$post = np_core_live_check();
	$text = wp_kses_post( wp_unslash( $_POST['text'] ?? '' ) );
	if ( '' === trim( $text ) ) { wp_send_json_error( __( 'Empty update', 'newspaper-core' ) ); }
	$entry   = array( 'id' => wp_generate_uuid4(), 'time' => time(), 'text' => $text );
	$entries = np_core_live_entries( $post );
	array_unshift( $entries, $entry );
	update_post_meta( $post, 'np_live_entries', $entries );
	wp_update_post( array( 'ID' => $post ) ); // bump "modified" time for SEO/schema.
	wp_send_json_success( array( 'row' => '<li data-id="' . esc_attr( $entry['id'] ) . '"><strong>' . esc_html( wp_date( 'j M, g:i A', $entry['time'] ) ) . '</strong> — ' . esc_html( wp_trim_words( wp_strip_all_tags( $text ), 20 ) ) . ' <button type="button" class="button-link-delete np-live-del">' . esc_html__( 'Delete', 'newspaper-core' ) . '</button></li>' ) );
} );

add_action( 'wp_ajax_np_live_del', function () {
	$post = np_core_live_check();
	$id   = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	update_post_meta( $post, 'np_live_entries', array_values( array_filter( np_core_live_entries( $post ), fn( $e ) => $e['id'] !== $id ) ) );
	wp_send_json_success();
} );

/* ---------- Public REST: /wp-json/np/v1/live/123?since=unix ---------- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'np/v1', '/live/(?P<id>\d+)', array(
		'methods' => 'GET', 'permission_callback' => '__return_true',
		'args' => array( 'id' => array( 'sanitize_callback' => 'absint' ), 'since' => array( 'sanitize_callback' => 'absint', 'default' => 0 ) ),
		'callback' => function ( WP_REST_Request $r ) {
			$id = (int) $r['id'];
			if ( 'publish' !== get_post_status( $id ) || ! get_post_meta( $id, 'np_live', true ) ) { return new WP_Error( 'np_not_live', 'Not found', array( 'status' => 404 ) ); }
			$new = array_values( array_filter( np_core_live_entries( $id ), fn( $e ) => $e['time'] > (int) $r['since'] ) );
			$res = rest_ensure_response( array( 'html' => implode( '', array_map( 'np_core_live_entry_html', $new ) ), 'latest' => $new ? $new[0]['time'] : (int) $r['since'] ) );
			$res->header( 'Cache-Control', 'public, max-age=30' );
			return $res;
		},
	) );
} );

/* ---------- Front end: entries above article content + 60s refresh ---------- */
add_filter( 'the_content', function ( $content ) {
	$id = get_the_ID();
	if ( ! $id || ! is_singular( 'post' ) || ! get_post_meta( $id, 'np_live', true ) ) { return $content; }
	$entries = np_core_live_entries( $id );
	$latest  = $entries ? $entries[0]['time'] : 0;
	$html  = '<section class="np-live" data-live="' . esc_attr( (string) $id ) . '" data-since="' . esc_attr( (string) $latest ) . '" data-endpoint="' . esc_url( rest_url( 'np/v1/live/' . $id ) ) . '">';
	$html .= '<p class="np-live__badge"><span></span> LIVE</p><ol class="np-live__list" aria-live="polite">' . implode( '', array_map( 'np_core_live_entry_html', $entries ) ) . '</ol></section>';
	$script = '<script>(function(){var s=document.querySelector(".np-live[data-live=\'' . (int) $id . '\']");if(!s||s.dataset.bound)return;s.dataset.bound=1;var l=s.querySelector("ol");setInterval(function(){if(document.hidden)return;fetch(s.dataset.endpoint+"?since="+s.dataset.since).then(function(r){return r.json()}).then(function(d){if(d&&d.html){l.insertAdjacentHTML("afterbegin",d.html);s.dataset.since=d.latest;}}).catch(function(){});},60000);})();</script>';
	$style = '<style>.np-live__badge{display:inline-flex;align-items:center;gap:6px;background:#c4161c;color:#fff;font-weight:800;padding:2px 10px;border-radius:3px}.np-live__badge span{width:8px;height:8px;border-radius:50%;background:#fff;animation:nplive 1s infinite}@keyframes nplive{50%{opacity:.2}}.np-live__list{list-style:none;padding:0;border-left:3px solid #c4161c;margin:12px 0 24px}.np-live__entry{padding:8px 0 8px 16px;border-bottom:1px solid #e3e3e3}.np-live__entry time{color:#c4161c;font-weight:700;font-size:.85rem}</style>';
	return $style . $html . $script . $content;
}, 20 );
