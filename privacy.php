<?php
/**
 * ConnectMe - Privacy Policy Page
 */

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <div class="glass-card">
        <h1 style="font-size: 2.2rem; margin-bottom: 20px;">Privacy Policy</h1>
        
        <div style="background: rgba(245, 158, 11, 0.15); border-left: 4px solid var(--warning); padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: #fef08a;">
            <strong>Notice:</strong> This is a standard placeholder Privacy Policy for ConnectMe. Please review and tailor with qualified legal counsel before launching to production.
        </div>

        <h3 style="margin-top: 20px; color: var(--primary);">1. Information We Collect</h3>
        <p style="color: var(--text-secondary); margin-bottom: 15px;">
            We collect information you provide directly during registration, including your name, email address, date of birth, gender, city, profile biography, interests, and uploaded photos. If you log in via Google OAuth, we collect your Google ID and email.
        </p>

        <h3 style="margin-top: 20px; color: var(--primary);">2. How We Use Information</h3>
        <p style="color: var(--text-secondary); margin-bottom: 15px;">
            Your information is used to curate your dating profile, display matches, facilitate messaging, process subscription payments, prevent fraud, and ensure age compliance (strictly 18+).
        </p>

        <h3 style="margin-top: 20px; color: var(--primary);">3. Information Sharing</h3>
        <p style="color: var(--text-secondary); margin-bottom: 15px;">
            We do not sell your personal information. Public profile details (Name, Age, City, Bio, Interests, Photos) are visible to other registered users in the Discover section.
        </p>

        <h3 style="margin-top: 20px; color: var(--primary);">4. Data Security</h3>
        <p style="color: var(--text-secondary); margin-bottom: 15px;">
            We employ industry-standard PDO prepared statements, password hashing (`password_hash`), secure HTTP cookies, and HTTPS encryption to protect your data.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
