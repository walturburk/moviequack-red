<div class="content">

<h4>Recommended by others</h4>

<?php foreach ($shares as $share): ?>
<div class="inblock">
<a href="/movie/<?= $share["movieid"] ?>">
    <img class="poster" src="/img/posters/<?= $share["poster"]; ?>">
</a>
<a class="block small centeralign" href="/user/<?= $share["user1"] ?>"><?= $share["user1"] ?></a>
</div>
<?php endforeach; ?>

</div>


<div class="content">

<h4>Bookmarks</h4>

<?php foreach ($bookmarks as $bookmark): ?>
<div class="inblock">
<a href="/movie/<?= $bookmark["item"] ?>">
    <img class="poster" src="/img/posters/<?= $bookmark["poster"]; ?>">
</a>
</div>
<?php endforeach; ?>

</div>


<div class="content">

<h4>Algo</h4>

<?php  foreach ($suggested as $suggest): ?>
<?php print_r($suggest); ?>
<div class="inblock">
<a href="/movie/<?= $suggest["movieid"] ?>">
    <img class="poster" src="/img/posters/<?= $suggest["poster"]; ?>">
</a>
</div>
<?php endforeach; ?>

</div>