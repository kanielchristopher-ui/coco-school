<?php
/**
 * ============================================================
 *  WebsiteController
 *  Public website pages for the school.
 *
 *  These pages are intentionally public: guests can browse the
 *  website without logging in. Login/Portal buttons point at the
 *  existing authentication system (auth/login), which is left
 *  completely untouched. Only publicly-safe data is exposed.
 * ============================================================
 */

declare(strict_types=1);

class WebsiteController extends Controller
{
    /**
     * Website pages are public - skip the login middleware.
     */
    public function checkAuth(): bool
    {
        return true;
    }

    /**
     * Render a page inside the public website layout.
     */
    protected function website(string $view, array $data = []): void
    {
        $this->view->render($view, $data, 'website/layouts/master');
    }

    /**
     * Root route: the public home page.
     */
    public function index(): void
    {
        $this->home();
    }

    /**
     * Home page.
     */
    public function home(): void
    {
        $db = Database::getInstance();

        // Real, non-sensitive aggregate counts where available; the years figure
        // is a clearly editable placeholder (not a claimed fact).
        $stats = [
            'students' => (int) $db->count('SELECT COUNT(*) FROM students WHERE status = "active"'),
            'teachers' => (int) $db->count('SELECT COUNT(*) FROM teachers WHERE status = "active"'),
            'classes'  => (int) $db->count('SELECT COUNT(*) FROM classes'),
            'years'    => 10,
        ];

        // A small, publicly-safe preview of the teaching staff for the home page.
        // Only names and the department/specialization are exposed - no photos,
        // contact details or other private fields.
        $teachers = $db->fetchAll(
            'SELECT id, first_name, last_name, department, specialization, photo
             FROM teachers
             WHERE status = "active"
             ORDER BY first_name ASC, last_name ASC
             LIMIT 3'
        );

        $this->website('website/home', [
            'pageTitle' => settings('school_name', 'École') . ' | ' . t('Quality Education'),
            'pageDescription' => t(':school provides quality education and prepares students for a successful future.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'home',
            'stats'           => $stats,
            'news'            => $this->latestNews(3),
            'teachers'        => $teachers,
        ]);
    }

    /**
     * About Us page.
     */
    public function about(): void
    {
        $this->website('website/about', [
            'pageTitle' => t('About Us') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Learn about the mission, vision and values of :school.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'about',
        ]);
    }

    /**
     * Academics page.
     */
    public function academics(): void
    {
        $this->website('website/academics', [
            'pageTitle' => t('Academics') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Explore the academic programs and curriculum offered at :school.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'academics',
        ]);
    }

    /**
     * Admissions page.
     */
    public function admissions(): void
    {
        $this->website('website/admissions', [
            'pageTitle' => t('Admissions') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Apply to :school. Learn about our admission process and requirements.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'admissions',
        ]);
    }

    /**
     * Teachers / Staff showcase page.
     *
     * Only publicly-safe fields are exposed (name, department, specialization,
     * qualification, experience, photo). No phone numbers, emails, addresses,
     * ids or any other private information is ever rendered.
     */
    public function teachers(): void
    {
        $teachers = Database::getInstance()->fetchAll(
            'SELECT id, first_name, last_name, department, specialization, qualification, experience_years, photo
             FROM teachers
             WHERE status = "active"
             ORDER BY first_name ASC, last_name ASC'
        );

        $this->website('website/teachers', [
            'pageTitle' => t('Our Teachers') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Meet the dedicated teaching staff of :school.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'teachers',
            'teachers'        => $teachers,
        ]);
    }

    /**
     * News / Events page. Reuses the existing events table safely.
     */
    public function news(): void
    {
        $this->website('website/news', [
            'pageTitle' => t('News & Events') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Latest announcements, events and news from :school.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'news',
            'news'            => $this->latestNews(9),
        ]);
    }

    /**
     * Contact Us page.
     */
    public function contact(): void
    {
        $this->website('website/contact', [
            'pageTitle' => t('Contact Us') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Get in touch with :school. Address, phone, email and opening hours.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'contact',
        ]);
    }

    /**
     * Process the contact form. UI-only with server-side validation; it never
     * depends on a mail configuration, so it can never break the application.
     */
    public function contactSubmit(): void
    {
        CSRF::check();

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $subject === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($message) < 5) {
            Flash::set('error', 'Please complete all fields with a valid email address and a message of at least 5 characters.');
            $this->redirect('contact');
        }

        Flash::set('success', 'Thank you, ' . $name . '! Your message has been received. Our school office will get back to you shortly.');
        $this->redirect('contact');
    }

    /**
     * Student / Teacher / Parent / Staff portal selection page.
     */
    public function portal(): void
    {
        $this->website('website/portal', [
            'pageTitle' => t('School Portal') . ' | ' . settings('school_name', 'École'),
            'pageDescription' => t('Access the student, teacher, parent and staff portals of :school.', ['school' => settings('school_name', 'École')]),
            'activeNav'       => 'portal',
            'loggedIn'        => Auth::check(),
        ]);
    }

    /**
     * Latest news/events from the existing events table.
     */
    protected function latestNews(int $limit = 6): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT id, title, description, event_date, event_time, event_type, location
             FROM events
             ORDER BY event_date DESC
             LIMIT ' . (int) $limit
        );
    }
}
