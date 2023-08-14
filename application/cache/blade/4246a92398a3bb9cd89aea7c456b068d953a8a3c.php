<?php
    $images         = collect($images);
    $first          = $images->shuffle()->shift();
    $cover_img      = blade_image($keyword,TRUE);
    $max_image      = MAX_IMAGE_RESULT;
    $ads_link       = ADS_LINK;
	 
?>
<article>
    <?php if($first): ?>
    <figure>
        <noscript>
            <img src="<?php echo e($cover_img); ?>" alt="<?php echo e($first['title']); ?>" width="640" height="360" />
        </noscript>
        <img class="v-cover ads-img" src="<?php echo e($cover_img); ?>" alt="<?php echo e($first['title']); ?>" width="100%" style="margin-right: 8px;margin-bottom: 8px;" />
        <figcaption><?php echo e($first['title']); ?> from <?php echo e($first['domain']); ?></figcaption>
    </figure>
    <?php endif; ?>
	
	
    <a href="/" target="_blank"><?php echo e($keyword); ?> - </a> <?php echo $sentences; ?>


</article>
<?php /**PATH C:\laragon\www\imake1\blade/export/_openai.blade.php ENDPATH**/ ?>