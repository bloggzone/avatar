<?php

?>
<?php echo '<' . '?' . "xml version='1.0' encoding='UTF-8'?>"; ?> <ns0:feed xmlns:ns0="http://www.w3.org/2005/Atom">
	<ns0:title type="html">wpan.com</ns0:title>
	<ns0:generator>Blogger</ns0:generator>
	<ns0:link href="http://localhost/wpan" rel="self" type="application/atom+xml" />
	<ns0:link href="http://localhost/wpan" rel="alternate" type="text/html" />
	<ns0:updated>2016-06-10T04:33:36Z</ns0:updated>
	<?php $__currentLoopData = $sub_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	<?php
		 $render 	= exportXML($info);

		 if(!$render){continue;}

		 $title 	= $render['title'];
		 $content 	= $render['content'];
		 $date 		= $render['date'];
		 $arr_tags 	= $render['arr_tags'];

	?>

	<ns0:entry>
		<?php $__currentLoopData = $arr_tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<?php if(strlen($tag)
		<= 3): ?> <?php continue; ?> <?php endif; ?> <ns0:category scheme="http://www.blogger.com/atom/ns#" term="<?php echo e($tag); ?>" />
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		<ns0:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/blogger/2008/kind#post" />
		<ns0:id>post-<?php echo e($key); ?></ns0:id>
		<ns0:author>
			<ns0:name>admin</ns0:name>
		</ns0:author>
		<ns0:content type="html">
			<![CDATA[<?php echo $content; ?>]]>
		</ns0:content>
		<ns0:published><?php echo e($date); ?></ns0:published>
		<ns0:title type="html">
			<![CDATA[<?php echo e($title); ?>]]>
		</ns0:title>
		<ns0:link href="http://localhost/wpan/<?php echo e($key); ?>/" rel="self" type="application/atom+xml" />
		<ns0:link href="http://localhost/wpan/<?php echo e($key); ?>/" rel="alternate" type="text/html" />
		</ns0:entry>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</ns0:feed><?php /**PATH C:\laragon\www\imake1\blade/export/blogspot.blade.php ENDPATH**/ ?>