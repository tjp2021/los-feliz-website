<?php
/**
 * Homepage columns and the Events archive.
 *
 * Instagram's Graph API returns each post's caption, and this theme already stores those
 * captions. Hashtag columns are a filter over that saved set. They are not a live hashtag
 * search, so a post only lands in a column when its caption contains one of the tags below.
 *
 * Tags (Settings → LFAR Instagram, written without #):
 *   event    #event #events          stays on the homepage until the event date has passed
 *   update   #update #updates
 *   urgent   the "calls for help" tags (default includes #urgentneed and the foster/volunteer tags)
 *
 * Event dates are read from the caption ("November 21, 2026", "Nov 21–22, 2026", "11/21/2026").
 * A post with a date is upcoming through the end of that day, then it moves to the past archive.
 * A post with no date stays in Upcoming only while it is the newest #event post, which is what
 * Melody described as "our event until our next event post." The homepage pins that next event
 * in the Event column so a newer kitten photo cannot push it off.
 *
 * @package lfar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Column tag lists. Urgent falls back to the calls-for-help list when its own field is blank. */
function lfar_ig_column_defs() {
	$settings = lfar_ig_settings();
	$saved    = isset( $settings['columns'] ) && is_array( $settings['columns'] ) ? $settings['columns'] : array();
	$urgent   = isset( $saved['urgent'] ) && '' !== $saved['urgent'] ? $saved['urgent'] : $settings['urgent_tags'];
	return array(
		'event'  => array(
			'label'  => 'Event',
			'class'  => '',
			'tags'   => lfar_ig_tag_list( isset( $saved['event'] ) ? $saved['event'] : 'event events' ),
			'kicker' => 'Next event',
			'empty'  => 'No event posted right now.',
		),
		'update' => array(
			'label'  => 'Update',
			'class'  => 'update',
			'tags'   => lfar_ig_tag_list( isset( $saved['update'] ) ? $saved['update'] : 'update updates' ),
			'kicker' => 'Latest update',
			'empty'  => 'No update posted right now.',
		),
		'urgent' => array(
			'label'  => 'Urgent need',
			'class'  => 'urgent',
			'tags'   => lfar_ig_tag_list( $urgent ),
			'kicker' => 'Urgent need',
			'empty'  => 'No urgent need posted right now.',
		),
	);
}

/**
 * End of the event day, as a timestamp, or 0 when the caption has no date.
 *
 * @param string $caption   Instagram caption.
 * @param int    $posted_ts When the post was published.
 */
function lfar_ig_event_end_ts( $caption, $posted_ts ) {
	$caption = html_entity_decode( (string) $caption, ENT_QUOTES, 'UTF-8' );
	$months  = array(
		'january' => 1, 'jan' => 1, 'february' => 2, 'feb' => 2, 'march' => 3, 'mar' => 3,
		'april' => 4, 'apr' => 4, 'may' => 5, 'june' => 6, 'jun' => 6, 'july' => 7, 'jul' => 7,
		'august' => 8, 'aug' => 8, 'september' => 9, 'sept' => 9, 'sep' => 9, 'october' => 10, 'oct' => 10,
		'november' => 11, 'nov' => 11, 'december' => 12, 'dec' => 12,
	);
	$alt   = implode( '|', array_keys( $months ) );
	$tz    = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
	$month = function ( $name ) use ( $months ) {
		$key = strtolower( $name );
		return isset( $months[ $key ] ) ? $months[ $key ] : 0;
	};
	$mk    = function ( $year, $mon, $day ) use ( $tz ) {
		if ( $mon < 1 || $mon > 12 || $day < 1 || $day > 31 ) {
			return 0;
		}
		$dt = DateTime::createFromFormat( '!Y-n-j', $year . '-' . $mon . '-' . $day, $tz );
		if ( ! $dt || $dt->format( 'Y-n-j' ) !== $year . '-' . $mon . '-' . $day ) {
			return 0;
		}
		$dt->setTime( 23, 59, 59 );
		return $dt->getTimestamp();
	};
	$span = '\b(' . $alt . ')\s+(\d{1,2})(?:st|nd|rd|th)?(?:\s*[–—-]\s*(?:(' . $alt . ')\s+)?(\d{1,2})(?:st|nd|rd|th)?)?';

	if ( preg_match( '/' . $span . '(?:,)?\s+(20\d{2})\b/iu', $caption, $m ) ) {
		$mon = $month( $m[1] );
		$day = (int) $m[2];
		if ( ! empty( $m[4] ) ) {
			$day = (int) $m[4];
			if ( ! empty( $m[3] ) ) {
				$mon = $month( $m[3] );
			}
		}
		return $mk( (int) $m[5], $mon, $day );
	}
	if ( preg_match( '/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/', $caption, $m ) ) {
		$year = (int) $m[3];
		if ( $year < 100 ) {
			$year += 2000;
		}
		return $mk( $year, (int) $m[1], (int) $m[2] );
	}
	if ( preg_match( '/' . $span . '\b/iu', $caption, $m ) ) {
		$year = (int) wp_date( 'Y', (int) $posted_ts );
		$mon  = $month( $m[1] );
		$day  = (int) $m[2];
		if ( ! empty( $m[4] ) ) {
			$day = (int) $m[4];
			if ( ! empty( $m[3] ) ) {
				$mon = $month( $m[3] );
			}
		}
		$end = $mk( $year, $mon, $day );
		if ( $end && $end < ( (int) $posted_ts - 14 * DAY_IN_SECONDS ) ) {
			$end = $mk( $year + 1, $mon, $day );
		}
		return $end;
	}
	return 0;
}

/**
 * Saved posts that carry any of $tags, newest first. Skips hidden posts and posts with no image yet.
 *
 * @param string[] $tags Lowercase hashtags, without '#'.
 * @return array<int,array>
 */
function lfar_ig_posts_with_tags( $tags ) {
	$tags   = array_flip( $tags );
	$hidden = array_flip( (array) get_option( 'lfar_ig_hidden', array() ) );
	$out    = array();
	foreach ( (array) get_option( 'lfar_ig_posts', array() ) as $p ) {
		if ( isset( $hidden[ $p['id'] ] ) || empty( $p['image'] ) || empty( $tags ) ) {
			continue;
		}
		$have = lfar_ig_hashtags( $p['caption'] );
		if ( array_intersect_key( $have, $tags ) ) {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * Upcoming and past event posts.
 *
 * @return array{upcoming:array,past:array}
 */
function lfar_ig_split_events() {
	$defs     = lfar_ig_column_defs();
	$now      = time();
	$upcoming = array();
	$past     = array();
	$posts    = lfar_ig_posts_with_tags( $defs['event']['tags'] );
	foreach ( $posts as $i => $p ) {
		$p['event_end'] = lfar_ig_event_end_ts( $p['caption'], $p['ts'] );
		$p['parsed']    = lfar_ig_parse_caption( $p['caption'] );
		$p['column']    = 'event';
		$p['urgent']    = false;
		if ( $p['event_end'] && $p['event_end'] < $now ) {
			$past[] = $p;
			continue;
		}
		if ( $p['event_end'] ) {
			$upcoming[] = $p;
			continue;
		}
		// No date in the caption: only the newest event post stays up, until a newer one replaces it.
		if ( 0 === $i ) {
			$upcoming[] = $p;
		} else {
			$p['event_end'] = (int) $p['ts'];
			$past[]         = $p;
		}
	}
	usort(
		$upcoming,
		function ( $a, $b ) {
			$ae = $a['event_end'] ? $a['event_end'] : PHP_INT_MAX;
			$be = $b['event_end'] ? $b['event_end'] : PHP_INT_MAX;
			return $ae === $be ? ( $b['ts'] <=> $a['ts'] ) : ( $ae <=> $be );
		}
	);
	usort(
		$past,
		function ( $a, $b ) {
			$ae = $a['event_end'] ? $a['event_end'] : (int) $a['ts'];
			$be = $b['event_end'] ? $b['event_end'] : (int) $b['ts'];
			return $be <=> $ae;
		}
	);
	return array(
		'upcoming' => $upcoming,
		'past'     => $past,
	);
}

/** The event pinned on the homepage: the soonest upcoming one. */
function lfar_ig_next_event() {
	$split = lfar_ig_split_events();
	return $split['upcoming'] ? $split['upcoming'][0] : null;
}

/**
 * One post per homepage column. Event is the pinned next event, not merely the newest post.
 *
 * @return array{event:?array,update:?array,urgent:?array}
 */
function lfar_ig_home_columns() {
	$defs     = lfar_ig_column_defs();
	$settings = lfar_ig_settings();
	$cutoff   = time() - max( 1, (int) $settings['urgent_days'] ) * DAY_IN_SECONDS;
	$used     = array();
	$out      = array(
		'event'  => null,
		'update' => null,
		'urgent' => null,
	);
	$next = lfar_ig_next_event();
	if ( $next ) {
		$out['event']         = $next;
		$used[ $next['id'] ]  = true;
	}
	foreach ( array( 'urgent', 'update' ) as $key ) {
		foreach ( lfar_ig_posts_with_tags( $defs[ $key ]['tags'] ) as $p ) {
			if ( isset( $used[ $p['id'] ] ) ) {
				continue;
			}
			if ( 'urgent' === $key && $p['ts'] < $cutoff ) {
				continue;
			}
			$p['parsed']    = lfar_ig_parse_caption( $p['caption'] );
			$p['column']    = $key;
			$p['urgent']    = 'urgent' === $key;
			$p['event_end'] = 0;
			$out[ $key ]    = $p;
			$used[ $p['id'] ] = true;
			break;
		}
	}
	return $out;
}

/** Clock or calendar icon used on the homepage cards. */
function lfar_ig_card_icon( $calendar ) {
	if ( $calendar ) {
		return '<svg viewbox="0 0 24 24"><rect height="16" rx="2" width="18" x="3" y="5"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>';
	}
	return '<svg viewbox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>';
}

/**
 * One homepage or events card.
 *
 * @param array  $p      Post from lfar_ig_home_columns() or lfar_ig_split_events().
 * @param string $column event | update | urgent.
 * @param bool   $past   Past-events archive.
 */
function lfar_ig_column_card( $p, $column, $past = false ) {
	$defs  = lfar_ig_column_defs();
	$def   = $defs[ $column ];
	$class = $def['class'] ? ' ' . $def['class'] : '';
	$tag   = $past ? 'Past event' : $def['label'];
	$pin   = ( 'event' === $column && ! $past ) ? ' is-pinned' : '';
	$when  = '';
	if ( 'event' === $column && ! empty( $p['event_end'] ) ) {
		$when = ( $past ? 'Held ' : '' ) . wp_date( 'F j, Y', $p['event_end'] );
	}
	if ( '' === $when ) {
		$when = 'Posted ' . wp_date( 'F j, Y', $p['ts'] );
	}
	$link = esc_url( $p['permalink'] );
	$more = $past ? 'View on Instagram' : ( 'event' === $column ? 'Event details' : ( 'urgent' === $column ? 'How to help' : 'View on Instagram' ) );
	return '<article class="news-card' . $pin . '" data-anim="fade-up">' . "\n"
		. '<a class="thumb" href="' . $link . '" target="_blank" rel="noopener"><span class="tag' . $class . '">' . esc_html( $tag ) . '</span>'
		. '<img alt="' . esc_attr( $p['parsed']['title'] ) . '" loading="lazy" src="' . esc_url( lfar_ig_img_url( $p ) ) . '"/>'
		. '<span class="overlay-mask">View post</span></a>' . "\n"
		. '<div class="news-body">' . "\n"
		. '<p class="col-kicker">' . esc_html( $past ? 'Past event' : $def['kicker'] ) . '</p>' . "\n"
		. '<div class="meta"><span>' . lfar_ig_card_icon( 'event' === $column ) . esc_html( $when ) . '</span></div>' . "\n"
		. '<h4>' . esc_html( $p['parsed']['title'] ) . '</h4>' . "\n"
		. ( $p['parsed']['text'] ? '<p>' . esc_html( $p['parsed']['text'] ) . '</p>' . "\n" : '' )
		. '<p class="lfar-a"><a class="more" href="' . $link . '" target="_blank" rel="noopener">' . esc_html( $more ) . ' <i>→</i></a></p>' . "\n"
		. "</div>\n</article>\n";
}

/** The three reserved homepage columns. An empty column keeps its place. */
function lfar_ig_home_columns_html() {
	$cols = lfar_ig_home_columns();
	$defs = lfar_ig_column_defs();
	$html = '<div class="wp-block-group news-grid news-columns">';
	foreach ( array( 'event', 'update', 'urgent' ) as $key ) {
		if ( $cols[ $key ] ) {
			$html .= lfar_ig_column_card( $cols[ $key ], $key );
		} else {
			$html .= '<article class="news-card' . ( 'event' === $key ? ' is-pinned' : '' ) . '"><div class="news-body">'
				. '<p class="col-kicker">' . esc_html( $defs[ $key ]['kicker'] ) . '</p>'
				. '<p class="events-empty">' . esc_html( $defs[ $key ]['empty'] ) . '</p></div></article>';
		}
	}
	return $html . '</div>';
}

/**
 * Upcoming or past events grid.
 *
 * @param bool $past Past archive when true.
 */
function lfar_ig_events_grid_html( $past ) {
	$split = lfar_ig_split_events();
	$posts = $past ? $split['past'] : $split['upcoming'];
	$class = $past ? 'events-past' : 'events-upcoming';
	if ( ! $posts ) {
		$note = $past
			? 'Past events will collect here after their date.'
			: 'No upcoming events right now.';
		return '<p class="events-empty ' . $class . '">' . esc_html( $note ) . '</p>';
	}
	$html = '<div class="wp-block-group news-grid ' . $class . '">';
	foreach ( $posts as $p ) {
		$html .= lfar_ig_column_card( $p, 'event', $past );
	}
	return $html . '</div>';
}
