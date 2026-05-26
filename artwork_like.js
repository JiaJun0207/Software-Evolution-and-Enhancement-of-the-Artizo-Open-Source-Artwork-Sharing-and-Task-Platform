function attachArtworkLikeHandlers() {
    document.querySelectorAll('.artwork-like-btn').forEach(function (button) {
        if (button.dataset.bound === '1') {
            return;
        }

        button.dataset.bound = '1';
        button.addEventListener('click', async function () {
            const artworkId = this.dataset.artworkId;
            this.disabled = true;

            try {
                const response = await fetch('artwork_like_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ artwork_id: artworkId })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    if (response.status === 401) {
                        window.location.href = 'login.php';
                        return;
                    }
                    throw new Error(data.message || 'Unable to update artwork like.');
                }

                document.querySelectorAll('.artwork-like-btn[data-artwork-id="' + artworkId + '"]').forEach(function (likeButton) {
                    likeButton.dataset.liked = data.liked ? '1' : '0';
                    likeButton.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                    likeButton.classList.toggle('liked', data.liked);

                    const icon = likeButton.querySelector('i');
                    if (icon) {
                        icon.className = data.liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                    }

                    const label = likeButton.querySelector('.artwork-like-label');
                    if (label) {
                        label.textContent = data.liked ? 'Unlike' : 'Like';
                    }

                    const count = likeButton.querySelector('.artwork-like-count');
                    if (count) {
                        count.textContent = data.like_count;
                    }
                });
            } catch (error) {
                alert(error.message);
            } finally {
                this.disabled = false;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', attachArtworkLikeHandlers);
