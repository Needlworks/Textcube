<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_NatureSkin {
	public static function convert($skinContent, $focus = 'global') {
		switch($focus) {
			case 'cover_rep':
				$skinContent = str_replace('CoverPage','[##_cover_content_##]',$skinContent);
				break;
			case 'list':
				$skinContent = str_replace('Keyword','[##_list_confirm_##]',$skinContent);
				$skinContent = str_replace('123','[##_list_count_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_list_atom_url_##]',$skinContent);
				break;
			case 'list_rep':
				$skinContent = str_replace('123','[##_list_rep_rp_cnt_##]',$skinContent);
				$skinContent = str_replace('Author','[##_list_rep_author_##]',$skinContent);
				$skinContent = str_replace('ListItem','[##_list_rep_title_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_list_rep_link_##]',$skinContent);
				$skinContent = str_replace('ListDate','[##_list_rep_regdate_##]',$skinContent);
				break;
			case 'rplist':
				$skinContent = str_replace('123','[##_rplist_count_##]',$skinContent);
				break;
			case 'rplist_rep':
				$skinContent = str_replace('Author','[##_rplist_rep_name_##]',$skinContent);
				$skinContent = str_replace('ListItem','[##_rplist_rep_body_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_rplist_rep_link_##]',$skinContent);
				$skinContent = str_replace('ListDate','[##_rplist_rep_regdate_##]',$skinContent);
				break;
			case 'tblist':
				$skinContent = str_replace('123','[##_tblist_count_##]',$skinContent);
				$skinContent = str_replace('Keyword','[##_tblist_confirm_##]',$skinContent);
				break;
			case 'tblist_rep':
				$skinContent = str_replace('Author','[##_tblist_rep_name_##]',$skinContent);
				$skinContent = str_replace('Subject','[##_tblist_rep_subject_##]',$skinContent);
				$skinContent = str_replace('Content','[##_tblist_rep_body_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_tblist_rep_link_##]',$skinContent);
				$skinContent = str_replace('ListDate','[##_tblist_rep_regdate_##]',$skinContent);
				break;
			case 'line':
				$skinContent = str_replace('#RSSLink/','[##_line_rssurl_##]',$skinContent);
				$skinContent = str_replace('#ATOMLink/','[##_line_atomurl_##]',$skinContent);
				$skinContent = str_replace('#MoreLink','[##_line_onclick_more_##]',$skinContent);
				break;
			case 'line_rep':
				$skinContent = str_replace('Author','[##_line_rep_name_##]',$skinContent);
				$skinContent = str_replace('LineSource','[##_line_rep_source_##]',$skinContent);
				$skinContent = str_replace('Content','[##_line_rep_content_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_line_rep_permalink_##]',$skinContent);
				$skinContent = str_replace('ListDate','[##_line_rep_regdate_##]',$skinContent);
				break;
			case 'local_spot_rep':
				$skinContent = str_replace('LocationName','[##_local_spot_##]',$skinContent);
				$skinContent = str_replace('20','[##_local_spot_depth_##]',$skinContent);
				break;
			case 'local_info_rep':
				$skinContent = str_replace('20','[##_local_info_depth_##]',$skinContent);
				$skinContent = str_replace('#Link','[##_local_info_link_##]',$skinContent);
				$skinContent = str_replace('Title','[##_local_info_title_##]',$skinContent);
				$skinContent = str_replace('Author','[##_local_info_author_##]',$skinContent);
				break;
			case 'tag_rep':
				$skinContent = str_replace('#Link','[##_tag_link_##]',$skinContent);
				$skinContent = str_replace('cloud3','[##_tag_class_##]',$skinContent);
				$skinContent = str_replace('Tag','[##_tag_name_##]',$skinContent);
				break;
			case 'paging':
				$skinContent = str_replace('href="#PrevLink"','[##_prev_page_##]',$skinContent);
				$skinContent = str_replace('href="#NextLink"','[##_next_page_##]',$skinContent);
				break;
			case 'paging_rep':
				$skinContent = str_replace('href="#Link"','[##_paging_rep_link_##]',$skinContent);
				$skinContent = str_replace('123','[##_paging_rep_link_num_##]',$skinContent);
				break;
			case 'random_tags':
				$skinContent = str_replace('#Link','[##_tag_link_##]',$skinContent);
				$skinContent = str_replace('cloud3','[##_tag_class_##]',$skinContent);
				$skinContent = str_replace('Tag','[##_tag_name_##]',$skinContent);
				break;
			case 'rct_notice_rep':
				$natureTags = array('#Link','Title','#AuthorLink','Author');
				$tcTags = array('[##_notice_rep_link_##]','[##_notice_rep_title_##]',
					'[##_notice_rep_author_link_##]','[##_notice_rep_author_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'search':
				$natureTags = array('SearchName','InputKeyword','#submit');
				$tcTags = array('[##_search_name_##]','[##_search_text_##]',
					'[##_search_onclick_submit_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'archive_rep':
				$natureTags = array('#Link','Date','123');
				$tcTags = array('[##_archive_rep_link_##]','[##_archive_rep_date_##]',
					'[##_archive_rep_count_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'author_rep':
				$natureTags = array('#Link','Author');
				$tcTags = array('[##_author_rep_link_##]','[##_author_rep_name_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'rctps_rep':
				$natureTags = array('#Link','Title','123','#AuthorLink','Author');
				$tcTags = array('[##_rctps_rep_link_##]','[##_rctps_rep_title_##]',
					'[##_rctps_rep_rp_cnt_##]','[##_rctps_rep_author_link_##]','[##_rctps_rep_author_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'rctrp_rep':
				$natureTags = array('#Link','Content','Name','Date');
				$tcTags = array('[##_rctrp_rep_link_##]','[##_rctrp_rep_desc_##]',
					'[##_rctrp_rep_name_##]','[##_rctrp_rep_time_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;
			case 'rcttb_rep':
				$natureTags = array('#Link','Content','Name','Date');
				$tcTags = array('[##_rcttb_rep_link_##]','[##_rcttb_rep_desc_##]',
					'[##_rcttb_rep_name_##]','[##_rcttb_rep_time_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				break;

			case 'article_rep':
				$natureTags = array('Title',
					'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus tempor sagittis diam hendrerit eleifend. Donec sed elit purus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus lectus felis, accumsan vel sagittis at, aliquam et ipsum. Aenean imperdiet orci quis nunc varius nec posuere tortor molestie. Proin non mauris mi. Quisque aliquet porttitor nulla, cursus ullamcorper magna ultricies fringilla.');
				$tcTags = array('[##_article_rep_title_##]','[##_article_rep_desc_##]');
				$skinContent = str_replace($natureTags, $tcTags,$skinContent);
				
			case 'global':
				$skinContent = str_replace('PageTitle','[##_title_##] <s_page_title> :: [##_page_post_title_##]</s_page_title>',$skinContent);
				$skinContent = str_replace('BlogTitle','[##_title_##]',$skinContent);
				$skinContent = str_replace('Description','[##_desc_##]',$skinContent);
				$skinContent = str_replace('BloggerName','[##_blogger_##]',$skinContent);
				$skinContent = str_replace('TextcubeName','[##_textcube_name_##]',$skinContent);
				$skinContent = str_replace('TextcubeVersion','[##_textcube_version_##]',$skinContent);

				$skinContent = str_replace('MetaKeywords','[##_meta_http_equiv_keywords_##]',$skinContent);
				$skinContent = str_replace('BodyId','[##_body_id_##]',$skinContent);
				$skinContent = str_replace('#BlogLink/','[##_blog_link_##]',$skinContent);
				$skinContent = str_replace('#BlogLink','[##_blog_link_##]',$skinContent);
				$skinContent = str_replace('#AdminLink','[##_owner_url_##]',$skinContent);
				$skinContent = str_replace('#RSSLink','[##_rss_url_##]',$skinContent);
				$skinContent = str_replace('#ResponseRSSLink','[##_response_rss_url_##]',$skinContent);
				$skinContent = str_replace('#ATOMLink','[##_atom_url_##]',$skinContent);
				$skinContent = str_replace('#ResponseATOMLink','[##_response_atom_url_##]',$skinContent);

				$skinContent = str_replace('T123','[##_count_total_##]',$skinContent);
				$skinContent = str_replace('D123','[##_count_today_##]',$skinContent);
				$skinContent = str_replace('B123','[##_count_yesterday_##]',$skinContent);

				$skinContent = str_replace('CalendarList','[##_calendar_##]',$skinContent);
				$skinContent = str_replace('CategoryList','[##_category_list_##]',$skinContent);
				$skinContent = str_replace('BookmarkList','[##_link_list_##]',$skinContent);

				break;
		}

		return $skinContent;
	}
}

?>
