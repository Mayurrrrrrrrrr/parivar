<?php
/**
 * परिवार फ़ीड — साझा संदेश और यादें
 */
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="border-color: var(--rang-pramukh);">
    <h3>साझा करें</h3>
    <form action="/api/feed.php?action=post" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <div class="form-group">
            <textarea name="sandesh" required placeholder="परिवार के साथ कुछ साझा करें..." style="height: 100px; resize: none;"></textarea>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: inline-block; cursor: pointer; color: var(--rang-pramukh); margin-bottom: 0;">
                    <i class="fa fa-image"></i> फोटो चुनें
                    <input type="file" name="photo" accept="image/*" style="display: none;">
                </label>
            </div>
            <button type="submit" style="width: auto;">पोस्ट करें</button>
        </div>
    </form>
</div>

<div id="full-feed">
    <p style="text-align: center; padding: 2rem;">लोड हो रहा है...</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadFeed();
    });

    function loadFeed() {
        fetch('/api/feed.php?action=list')
            .then(r => r.json())
            .then(res => {
                const container = document.getElementById('full-feed');
                if (res.safalta && res.data.length > 0) {
                    container.innerHTML = res.data.map(p => `
                        <div class="card feed-post">
                            <div style="display: flex; justify-content: space-between;">
                                <div class="feed-meta">
                                    <strong style="color: var(--rang-uprang); font-size: 1.1rem;">${p.user_naam}</strong><br>
                                    <span>${p.banaya_at}</span>
                                </div>
                                ${p.user_id == <?php echo $_SESSION['user_id']; ?> ? `<button onclick="deletePost(${p.id})" style="background: none; color: #ccc; width: auto; padding: 0; font-size: 0.8rem;"><i class="fa fa-trash"></i></button>` : ''}
                            </div>
                            ${p.photo_url ? `<div style="margin-top: 1rem;"><img src="${p.photo_url}" style="width: 100%; border-radius: 8px; max-height: 400px; object-fit: cover;"></div>` : ''}
                            <div style="margin-top: 1rem; font-size: 1.1rem; color: var(--rang-path);">${p.sandesh}</div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="card" style="text-align: center;">अभी तक कोई पोस्ट नहीं है। अपनी पहली याद साझा करें!</div>';
                }
            });
    }

    function deletePost(id) {
        if (!confirm('क्या आप यह पोस्ट हटाना चाहते हैं?')) return;
        fetch(`/api/feed.php?action=delete&id=${id}`, { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (res.safalta) loadFeed();
                else alert(res.sandesh);
            });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
