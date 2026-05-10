<?php
/**
 * परिवार फ़ीड (v2.0)
 */
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$parivar_id = currentParivarId();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT f.*, u.naam as user_naam, 
    (SELECT COUNT(*) FROM parivar_feed_reactions WHERE post_id = f.id AND emoji = '🙏') as r1_count,
    (SELECT COUNT(*) FROM parivar_feed_reactions WHERE post_id = f.id AND emoji = '❤️') as r2_count,
    (SELECT COUNT(*) FROM parivar_feed_reactions WHERE post_id = f.id AND emoji = '😊') as r3_count
    FROM parivar_feed f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.parivar_id = ? 
    ORDER BY f.banaya_at DESC
");
$stmt->execute([$parivar_id]);
$feeds = $stmt->fetchAll();
?>

<header class="app-header">
    <h1>📢 परिवार फ़ीड</h1>
</header>

<div class="page-content">
    
    <!-- New Post Box -->
    <div class="card" style="background:var(--bg-card); border-radius:16px; padding:16px; margin-bottom:20px; border:0.5px solid var(--seemant);">
        <form action="/parivar/api/feed.php?action=banao" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <textarea name="sandesh" class="form-control" placeholder="आज क्या साझा करना चाहते हैं?" style="min-height:80px; border:none; background:transparent; padding:0;"></textarea>
            <div class="divider"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <label style="cursor:pointer; color:var(--rang-pramukh); font-size:20px;">
                    <i class="ti ti-camera"></i>
                    <input type="file" name="photo" style="display:none">
                </label>
                <button type="submit" class="btn btn-primary" style="width:auto; padding:6px 20px;">साझा करें</button>
            </div>
        </form>
    </div>

    <!-- Feed Items -->
    <?php foreach ($feeds as $f): ?>
        <div class="feed-card" id="post-<?php echo $f['id']; ?>">
            <div class="feed-card-header">
                <div class="avatar avatar-sm avatar-purple"><?php echo mb_substr($f['user_naam'], 0, 1); ?></div>
                <div>
                    <div style="font-size:13px; font-weight:500"><?php echo s($f['user_naam']); ?></div>
                    <div style="font-size:10px; color:var(--text-muted)"><?php echo time_ago($f['banaya_at']); ?></div>
                </div>
            </div>
            <div class="feed-card-body"><?php echo nl2br(s($f['sandesh'])); ?></div>
            <?php if ($f['photo_url']): ?>
                <img src="/parivar/<?php echo $f['photo_url']; ?>" class="feed-card-photo">
            <?php endif; ?>
            
            <div class="feed-reactions">
                <button class="reaction-btn" onclick="react(<?php echo $f['id']; ?>, '🙏')">🙏 <span id="r1-<?php echo $f['id']; ?>"><?php echo $f['r1_count']; ?></span></button>
                <button class="reaction-btn" onclick="react(<?php echo $f['id']; ?>, '❤️')">❤️ <span id="r2-<?php echo $f['id']; ?>"><?php echo $f['r2_count']; ?></span></button>
                <button class="reaction-btn" onclick="react(<?php echo $f['id']; ?>, '😊')">😊 <span id="r3-<?php echo $f['id']; ?>"><?php echo $f['r3_count']; ?></span></button>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script>
    function react(postId, emoji) {
        fetch('/parivar/api/feed.php?action=react', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&emoji=${emoji}&csrf_token=<?php echo csrf_token(); ?>`
        })
        .then(r => r.json())
        .then(data => {
            if (data.safalta) {
                // Refresh counts or just show visual confirmation
                location.reload();
            }
        });
    }
</script>

<?php include '../includes/nav.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
