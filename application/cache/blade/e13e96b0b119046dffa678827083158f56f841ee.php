<?php
$author = 'Admin';
$site_url = 'http://example.com/';
$backdate = BACK_DATE;
$schedule = SHEDULE_DATE;
$category = $niche??WP_CATEGORY;
$site_title = blade_sitename($category);
?>

<?php echo '<' . '?' . "xml version='1.0' encoding='UTF-8'?>"; ?> <rss version="2.0" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.2/">

	<channel>
		<title>My Site</title>
		<link>http://example.com/</link>
		<description></description>
		<pubDate>Thu, 28 May 2009 16:06:40 +0000</pubDate>
		<wp:author>
			<wp:author_id>1</wp:author_id>
			<wp:author_login>
				<![CDATA[admin]]>
			</wp:author_login>
			<wp:author_email>
				<![CDATA[admin@vyant.cc]]>
			</wp:author_email>
			<wp:author_display_name>
				<![CDATA[admin]]>
			</wp:author_display_name>
			<wp:author_first_name>
				<![CDATA[]]>
			</wp:author_first_name>
			<wp:author_last_name>
				<![CDATA[]]>
			</wp:author_last_name>
		</wp:author>

		<generator>http://wordpress.org/?v=2.7.1</generator>
		<language>en</language>
		<wp:wxr_version>1.0</wp:wxr_version>
		<wp:base_site_url>http://example.com/</wp:base_site_url>
		<wp:base_blog_url>http://example.com/</wp:base_blog_url>

		<?php $__currentLoopData = $sub_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post_id => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<?php

		 $render 	= exportXML($info);

		 if(!$render){continue;}

		 $keyword 	= $info['keyword'];
		 $title 	= $render['title'];
		 $slug 		= $render['slug'];
		 $content 	= $render['content'];
		 $date 		= $render['date'];
		 $arr_tags 	= $render['arr_tags'];

		 $rawKw 		= rawurlencode(convert_accented_characters($keyword));
		 $thumb_image 	= "https://tse1.mm.bing.net/th?q={$rawKw}";

		?>

		<item>
			<title>
				<![CDATA[<?php echo e($title); ?>]]>
			</title>

			<link><?php echo e($site_url); ?><?php echo e($slug); ?>/</link>
			<pubDate><?php echo e($date); ?></pubDate>

			<dc:creator>
				<![CDATA[<?php echo e($author); ?>]]>
			</dc:creator>
			<wp:postmeta>
				<wp:meta_key>_byline</wp:meta_key>
				<wp:meta_value><?php echo e($author); ?></wp:meta_value>
			</wp:postmeta>

			<?php
			$category = trim( $category );
			$cat_slug = slug_imake($category);
			?>

			<category>
				<![CDATA[<?php echo e($category); ?>]]>
			</category>
			<category domain="category" nicename="<?php echo e($cat_slug); ?>">
				<![CDATA[<?php echo e($category); ?>]]>
			</category>

			<?php $__currentLoopData = collect($arr_tags)->shuffle()->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<?php if(strlen($tag) <= 3): ?> <?php continue; ?> <?php endif; ?> <category domain="tag" nicename="<?php echo e(url_title( $tag ,'-' )); ?>">
				<![CDATA[<?php echo e($tag); ?>]]>
				</category>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

				<guid isPermaLink="false"><?php echo e($site_url); ?>?p=<?php echo e($post_id); ?></guid>
				<description></description>
				<content:encoded>
					<![CDATA[<?php echo $content; ?>]]>
				</content:encoded>
				<excerpt:encoded>
					<![CDATA[]]>
				</excerpt:encoded>
				<wp:post_id><?php echo e($post_id); ?></wp:post_id>
				<wp:post_date_gmt><?php echo e($date); ?></wp:post_date_gmt>
				<wp:comment_status>open</wp:comment_status>
				<wp:ping_status>closed</wp:ping_status>
				<wp:post_name><?php echo e($slug); ?></wp:post_name>

				<wp:status>publish</wp:status>
				<wp:post_parent>0</wp:post_parent>
				<wp:menu_order>0</wp:menu_order>
				<wp:post_type>post</wp:post_type>
				<wp:post_password></wp:post_password>

				<wp:postmeta>
					<wp:meta_key>_old_id</wp:meta_key>
					<wp:meta_value><?php echo e($post_id); ?></wp:meta_value>
				</wp:postmeta>

				<wp:postmeta>
					<wp:meta_key><![CDATA[rank_math_focus_keyword]]></wp:meta_key>
					<wp:meta_value><![CDATA[<?php echo e($category); ?>]]></wp:meta_value>
				</wp:postmeta>

				<wp:postmeta>
					<wp:meta_key><![CDATA[fifu_image_url]]></wp:meta_key>
					<wp:meta_value><![CDATA[<?php echo e($thumb_image); ?>]]></wp:meta_value>
				</wp:postmeta>

				<wp:postmeta>
					<wp:meta_key>
						<![CDATA[json_ld]]>
					</wp:meta_key>
					<wp:meta_value>
						<![CDATA[<script type="application/ld+json">
				{
				  "@context": "https://schema.org/", 
				  "@type": "Article", 
				  "author": {
				    "@type": "Person",
				    "name": "<?php echo e($author); ?>"
				  },
				  "headline": "<?php echo e($title); ?>",
				  "datePublished": "<?php echo e(date('Y-m-d')); ?>",
				  "image": "<?php echo e($thumb_image); ?>",
				  "publisher": {
				    "@type": "Organization",
				    "name": "<?php echo e($site_title); ?>",
				    "logo": {
				      "@type": "ImageObject",
				      "url": "https://via.placeholder.com/512.png?text=<?php echo e(rawurlencode(convert_accented_characters($keyword))); ?>",
				      "width": 512,
				      "height": 512
				    }
				  }
				}
				</script>]]>
					</wp:meta_value>
				</wp:postmeta>

				</item>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

	</channel>
	</rss><?php /**PATH C:\laragon\www\imake1\blade/export/wordpress.blade.php ENDPATH**/ ?>