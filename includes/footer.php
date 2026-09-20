<?php
/**
 * ConnectMe - Global Footer Template
 */
?>
    <!-- Mobile Bottom Navigation Bar (Visible on Small Screens when Logged In) -->
    <?php if (isLoggedIn()): ?>
        <nav class="mobile-nav">
            <a href="<?php echo APP_URL; ?>/user/discover.php" class="nav-item-link <?php echo strpos($_SERVER['PHP_SELF'], 'discover.php') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-fire"></i>
                <span>Discover</span>
            </a>
            <a href="<?php echo APP_URL; ?>/user/matches.php" class="nav-item-link <?php echo strpos($_SERVER['PHP_SELF'], 'matches.php') !== false || strpos($_SERVER['PHP_SELF'], 'chat.php') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Matches</span>
            </a>
            <a href="<?php echo APP_URL; ?>/user/likes.php" class="nav-item-link <?php echo strpos($_SERVER['PHP_SELF'], 'likes.php') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-heart"></i>
                <span>Likes</span>
            </a>
            <a href="<?php echo APP_URL; ?>/user/subscribe.php" class="nav-item-link <?php echo strpos($_SERVER['PHP_SELF'], 'subscribe.php') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-crown" style="color: var(--gold);"></i>
                <span>Premium</span>
            </a>
            <a href="<?php echo APP_URL; ?>/user/profile.php" class="nav-item-link <?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false ? 'active' : ''; ?>">
                <i class="fa-solid fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    <?php endif; ?>

    <!-- Main Page Footer -->
    <footer style="margin-top: auto; background: #090d16; border-top: 1px solid var(--border-color); padding: 40px 20px 20px 20px; color: var(--text-secondary); font-size: 0.9rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            
            <!-- Global Dating Safety Warning Notice -->
            <div class="security-banner">
                <i class="fa-solid fa-shield-halved" style="font-size: 1.2rem; color: #f59e0b;"></i>
                <div>
                    <strong>Safety First:</strong> Never send money, OTPs, passwords, bank details or card information to another user. Report suspicious activity immediately.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 30px;">
                <div>
                    <h4 style="color: white; margin-bottom: 15px; font-size: 1.2rem;"><i class="fa-solid fa-heart-pulse" style="color: var(--primary);"></i> ConnectMe</h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">
                        ConnectMe is designed for adults (18+) to find real connections safely and comfortably.
                    </p>
                </div>
                <div>
                    <h5 style="color: white; margin-bottom: 12px;">Quick Links</h5>
                    <ul style="list-style: none; padding: 0; line-height: 2;">
                        <li><a href="<?php echo APP_URL; ?>/index.php" style="color: var(--text-secondary);">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/user/discover.php" style="color: var(--text-secondary);">Discover People</a></li>
                        <li><a href="<?php echo APP_URL; ?>/user/subscribe.php" style="color: var(--text-secondary);">Premium Plans</a></li>
                    </ul>
                </div>
                <div>
                    <h5 style="color: white; margin-bottom: 12px;">Safety & Legal</h5>
                    <ul style="list-style: none; padding: 0; line-height: 2;">
                        <li><a href="<?php echo APP_URL; ?>/safety.php" style="color: var(--text-secondary);"><i class="fa-solid fa-shield"></i> Dating Safety Guide</a></li>
                        <li><a href="<?php echo APP_URL; ?>/privacy.php" style="color: var(--text-secondary);">Privacy Policy</a></li>
                        <li><a href="<?php echo APP_URL; ?>/terms.php" style="color: var(--text-secondary);">Terms of Service (18+)</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contact.php" style="color: var(--text-secondary);">Contact Support</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved. Strictly 18+ Platform.
            </div>
        </div>
    </footer>

    <!-- App JavaScript Engine -->
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
</body>
</html>
