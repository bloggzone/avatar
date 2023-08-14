<?php

shuffle($images);
sort($images);

shuffle($sentences);
sort($sentences);


$first          = array_shift($images);

$keyword        = $first['keyword'];
$slug           = $first['slug'];
$f_image        = $first['url'];
$f_domain       = $first['domain'];
$f_title        = url_title($first['title'],' ');

?>
<article>
<p align="justify"><strong><?= ucwords($keyword) ?></strong>. <?= random_sentences($sentences,0,2) ?></p>
<figure>
    <noscript>
        <img src="<?= $f_image ?>" alt="<?= $keyword ?>" />
    </noscript>
    <img class="v-cover ads-img" src="<?= $f_image ?>" alt="<?= $keyword ?>" width="100%" onerror="this.onerror=null;this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQh_l3eQ5xwiPy07kGEXjmjgmBKBRB7H2mRxCGhv1tFWg5c_mWT';" />
    <figcaption><small>Source : <?= $f_domain ?></small></figcaption>
</figure>
<p align="justify">
    <?= random_sentences($sentences,2,2) ?>
</p>
<h3><?= ucwords($f_title) ?></h3>
<p align="justify"><?= random_sentences($sentences,4,2) ?></p>
<!--more-->
</article>

<section>
<?php
foreach ($images as $key => $info)
{

if($key == 12){break;}

$no             = 1 + $key;

$i_title        = $info['title'];
$i_image        = $info['url'];
$i_domain       = $info['domain'];

$i_title        = url_title($i_title,' ',TRUE);
$i_title        = ucfirst($i_title);
$i_title_html   = htmlspecialchars($i_title);


?>
<aside>
    <figure>
        <img class="v-image" alt="<?= $i_title_html ?>" src="<?= $i_image ?>" width="100%" onerror="this.onerror=null;this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQh_l3eQ5xwiPy07kGEXjmjgmBKBRB7H2mRxCGhv1tFWg5c_mWT';" />
        <figcaption><small>Source: <?= $i_domain ?></small></figcaption>
    </figure>
    <p align="center"><b><?= $i_title ?></b>. <?= character_limiter(random_sentences($sentences,$key,1), 120,'.'); ?></p>
</aside>
<?php } ?>
</section>


<?php if(count($sentences) > 10){ ?>
<section>
    <h3><?= ucwords($keyword) ?></h3>
    <?php

    $end_sentences  = array_slice($sentences,10);

    $sub_sentences  = array_chunk($end_sentences,4);

    $p = '';

    foreach ($sub_sentences as $key => $p_arr)
    {
        //if($key == 8){break;}

        $k = '';

        foreach ($p_arr as $key => $txt) {
            $k .= ($key == 0)?"<strong>{$txt}</strong>":$txt;
        }

        $p .="<p align='justify'>{$k}</p>";
    }

    echo $p;

    ?>
</section>
<?php } ?>
