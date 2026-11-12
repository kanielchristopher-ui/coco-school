<?php
/**
 * ============================================================
 *  ProfileController
 *  View and update the logged-in user's own profile.
 * ============================================================
 */

declare(strict_types=1);

class ProfileController extends Controller
{
    public function index(): void
    {
        $user = (new User())->find(Auth::id());
        if (!$user) {
            $this->redirect('auth/login');
        }

        $teacherProfile = null;
        if (Auth::role() === 'teacher') {
            $teacherProfile = (new Teacher())->firstWhere('user_id', $user['id']);
        }

        $this->render('users/profile', [
            'user'           => $user,
            'teacherProfile' => $teacherProfile,
        ]);
    }

    /**
     * Update profile details.
     */
    public function update(): void
    {
        CSRF::check();

        // Profile editing is disabled for teacher and student accounts.
        if (in_array(Auth::role(), ['teacher', 'student'], true)) {
            Flash::set('error', 'Profile editing is not available for your account.');
            $this->redirect('profile/index');
        }

        $user = (new User())->find(Auth::id());
        if (!$user) {
            $this->redirect('auth/login');
        }

        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $data = ['first_name' => $first, 'last_name' => $last, 'phone' => $phone];

        // Optional photo upload.
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_IMAGE_TYPES, true) && $_FILES['photo']['size'] <= MAX_UPLOAD_SIZE) {
                $dir = UPLOAD_PATH . '/users';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $filename = 'user_' . $user['id'] . '_' . date('Ymd_His') . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $filename)) {
                    $data['photo'] = 'uploads/users/' . $filename;
                }
            }
        }

        (new User())->update($user['id'], $data);

        // Refresh session name.
        $_SESSION['user_name'] = trim($first . ' ' . $last);
        if (!empty($data['photo'])) {
            $_SESSION['user_photo'] = $data['photo'];
        }

        $this->logActivity('update', 'profile', 'Updated own profile');
        Flash::set('success', 'Profile updated.');
        $this->redirect('profile/index');
    }

    /**
     * Change the account password.
     */
    public function changePassword(): void
    {
        CSRF::check();

        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $user = (new User())->find(Auth::id());
        if (!$user || !password_verify($current, $user['password'])) {
            Flash::set('error', 'Current password is incorrect.');
            $this->redirect('profile/index');
        }
        if (mb_strlen($new) < MIN_PASSWORD_LENGTH) {
            Flash::set('error', 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
            $this->redirect('profile/index');
        }
        if ($new !== $confirm) {
            Flash::set('error', 'New passwords do not match.');
            $this->redirect('profile/index');
        }

        (new User())->update($user['id'], ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        $this->logActivity('password_change', 'profile', 'Changed own password');
        Flash::set('success', 'Password changed successfully.');
        $this->redirect('profile/index');
    }
}
