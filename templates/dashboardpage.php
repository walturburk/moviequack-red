<div class="content">

<h4>Recommended by others</h4>

<?php foreach ($shares as $share): ?>
<div class="inblock">
<a href="/movie/<?= $share["movieid"] ?>">
    <img class="poster" src="<?= basethumburl.$share["poster"]; ?>">
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
    <img class="poster" src="<?= basethumburl.$bookmark["poster"]; ?>">
</a>
</div>
<?php endforeach; ?>

</div>


<div class="content">

<h4>Algo</h4>

<?php foreach ($suggested as $suggest): ?>
<div class="inblock">
<a href="/movie/<?= $suggest["item"] ?>">
    <img class="poster" src="<?= basethumburl.$suggest["poster"]; ?>">
</a>
</div>
<?php endforeach; ?>

</div>