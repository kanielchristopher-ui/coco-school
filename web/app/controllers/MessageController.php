<?php
/**
 * ============================================================
 *  MessageController
 *  Internal messaging and notifications.
 * ============================================================
 */

declare(strict_types=1);

class MessageController extends Controller
{
    protected Message $message;

    public function __construct()
    {
        parent::__construct();
        $this->message = new Message();
    }

    public function inbox(): void
    {
        Auth::requireAny(['send_student_messages', 'view_teacher_messages', 'messaging.manage']);
        $this->render('messages/inbox', ['messages' => $this->message->inbox(Auth::id())]);
    }

    /**
     * Compose page. Teachers may only message students in their assigned
     * classes; other messaging-capable roles can pick any user.
     */
    public function compose(): void
    {
        Auth::requireAny(['send_student_messages', 'messaging.manage']);

        $users = [];
        if (Auth::role() === 'teacher') {
            $assigned = Auth::assignedClassIds();
            if ($assigned) {
                $in = implode(',', array_map('intval', $assigned));
                $users = Database::getInstance()->fetchAll(
                    "SELECT u.id, u.first_name, u.last_name, u.username, u.role_id
                     FROM users u
                     INNER JOIN students s ON s.user_id = u.id
                     WHERE s.class_id IN ($in) AND s.status = 'active'
                     ORDER BY u.first_name"
                );
            }
        } else {
            $users = Database::getInstance()->fetchAll(
                'SELECT id, first_name, last_name, username, role_id FROM users WHERE id != ? ORDER BY first_name',
                [Auth::id()]
            );
        }

        $this->render('messages/compose', ['users' => $users]);
    }

    public function send(): void
    {
        CSRF::check();
        Auth::requireAny(['send_student_messages', 'messaging.manage']);

        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if (!$receiverId || $body === '') {
            Flash::set('error', 'Receiver and message body are required.');
            $this->redirect('messages/compose');
        }

        // Teachers may only message students in their assigned classes.
        if (Auth::role() === 'teacher') {
            $assigned = Auth::assignedClassIds();
            $allowed = $assigned && Database::getInstance()->fetch(
                'SELECT s.id FROM students s
                 WHERE s.user_id = ? AND s.class_id IN (' . implode(',', array_map('intval', $assigned)) . ') AND s.status = "active"',
                [$receiverId]
            );
            if (!$allowed) {
                Flash::set('error', 'You can only message students in your assigned classes.');
                $this->redirect('messages/compose');
            }
        }

        $this->message->create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $receiverId,
            'subject'     => $subject,
            'body'        => $body,
        ]);

        $this->logActivity('send', 'messaging', 'Sent message to user #' . $receiverId);
        Flash::set('success', 'Message sent.');
        $this->redirect('messages/inbox');
    }

    /**
     * Broadcast form. Teachers may announce to students in their assigned
     * classes; messaging managers can target a role, a class, or all users.
     */
    public function broadcast(): void
    {
        Auth::requireAny(['send_student_messages', 'messaging.manage']);

        $classes = [];
        if (Auth::role() === 'teacher') {
            $assigned = Auth::assignedClassIds();
            if ($assigned) {
                $in = implode(',', array_map('intval', $assigned));
                $classes = Database::getInstance()->fetchAll(
                    "SELECT * FROM classes WHERE id IN ($in) ORDER BY name"
                );
            }
        } else {
            $classes = Database::getInstance()->fetchAll('SELECT * FROM classes ORDER BY name');
        }

        $this->render('messages/broadcast', [
            'classes' => $classes,
            'isTeacher' => Auth::role() === 'teacher',
        ]);
    }

    /**
     * Send a broadcast to a group of recipients.
     */
    public function broadcastSend(): void
    {
        CSRF::check();
        Auth::requireAny(['send_student_messages', 'messaging.manage']);

        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if ($subject === '' || $body === '') {
            Flash::set('error', 'Subject and message body are required.');
            $this->redirect('messages/broadcast');
        }

        $recipientIds = $this->broadcastRecipients();

        if (!$recipientIds) {
            Flash::set('error', 'No recipients matched the selected target.');
            $this->redirect('messages/broadcast');
        }

        $sent = 0;
        $notification = new Notification();
        foreach ($recipientIds as $userId) {
            $msgId = $this->message->create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $userId,
                'subject'     => $subject,
                'body'        => $body,
            ]);
            $notification->push($userId, $subject, $body, 'info', $msgId ? 'messages/read/' . $msgId : null);
            $sent++;
        }

        $this->logActivity('broadcast', 'messaging', 'Broadcast "' . $subject . '" to ' . $sent . ' recipient(s)');
        Flash::set('success', 'Broadcast sent to ' . $sent . ' recipient(s).');
        $this->redirect('messages/inbox');
    }

    /**
     * Resolve the recipient user ids for the current broadcast form.
     */
    protected function broadcastRecipients(): array
    {
        $db = Database::getInstance();

        // Teachers may only target their own assigned classes.
        if (Auth::role() === 'teacher') {
            $classId = (int) ($_POST['class_id'] ?? 0);
            $assigned = Auth::assignedClassIds();
            if (!$classId || !in_array($classId, $assigned, true)) {
                return [];
            }
            return array_map('intval', array_column(
                $db->fetchAll(
                    'SELECT u.id FROM students s
                     INNER JOIN users u ON u.id = s.user_id
                     WHERE s.class_id = ? AND s.status = "active" AND u.status = "active"',
                    [$classId]
                ),
                'id'
            ));
        }

        // Managers: a class (all students), a role, or everyone.
        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId) {
            return array_map('intval', array_column(
                $db->fetchAll(
                    'SELECT u.id FROM students s
                     INNER JOIN users u ON u.id = s.user_id
                     WHERE s.class_id = ? AND s.status = "active" AND u.status = "active"',
                    [$classId]
                ),
                'id'
            ));
        }

        $role = trim($_POST['role'] ?? '');
        if ($role !== '') {
            $roleRow = $db->fetch('SELECT id FROM roles WHERE slug = ?', [$role]);
            if (!$roleRow) {
                return [];
            }
            return array_map('intval', array_column(
                $db->fetchAll('SELECT id FROM users WHERE role_id = ? AND status = "active"', [(int) $roleRow['id']]),
                'id'
            ));
        }

        return array_map('intval', array_column(
            $db->fetchAll('SELECT id FROM users WHERE status = "active"'),
            'id'
        ));
    }

    /**
     * Read a conversation thread.
     */
    public function read(int $id): void
    {
        Auth::requireAny(['send_student_messages', 'view_teacher_messages', 'messaging.manage']);

        $message = $this->message->find($id);
        if (!$message || ($message['receiver_id'] !== Auth::id() && $message['sender_id'] !== Auth::id())) {
            $this->renderError('Message not found.');
            return;
        }

        // Mark as read if we are the receiver.
        if ($message['receiver_id'] === Auth::id()) {
            $this->message->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        }

        $thread = $this->message->conversation($message['sender_id'], $message['receiver_id']);
        $this->render('messages/read', [
            'messages' => $thread,
            'otherId'  => $message['sender_id'] === Auth::id() ? $message['receiver_id'] : $message['sender_id'],
        ]);
    }

    /**
     * Reply within a conversation.
     */
    public function reply(int $otherId): void
    {
        CSRF::check();

        // Admins and messaging managers may reply to anyone. Students may reply
        // only within their automatic network: admins and the teachers assigned
        // to their own class. No manual linking is involved.
        if (!Auth::can('send_student_messages') && !Auth::can('messaging.manage')) {
            Auth::requirePermission('view_teacher_messages');

            $sender = Database::getInstance()->fetch(
                'SELECT u.id, r.slug AS role_slug
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id
                 WHERE u.id = ? AND u.status = "active"',
                [$otherId]
            );
            $student = Database::getInstance()->fetch(
                'SELECT s.class_id FROM students s WHERE s.user_id = ? LIMIT 1',
                [Auth::id()]
            );

            $allowed = $sender && in_array($sender['role_slug'], ['admin', 'super_admin'], true);
            if (!$allowed && $sender && $student) {
                $allowed = (bool) Database::getInstance()->fetch(
                    'SELECT t.id
                     FROM teachers t
                     WHERE t.user_id = ?
                       AND (
                         t.id = (SELECT class_teacher_id FROM classes WHERE id = ?)
                         OR t.id IN (SELECT teacher_id FROM subjects WHERE class_id = ?)
                       )',
                    [$otherId, $student['class_id'], $student['class_id']]
                );
            }

            if (!$allowed) {
                Flash::set('error', 'You can only reply to admins or teachers of your class.');
                $this->redirect('messages/inbox');
                return;
            }
        }

        $body = trim($_POST['body'] ?? '');
        if ($body !== '') {
            $this->message->create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $otherId,
                'subject'     => 'Re: ' . trim($_POST['subject'] ?? ''),
                'body'        => $body,
            ]);
            Flash::set('success', 'Reply sent.');
        }
        $this->redirect('messages/inbox');
    }

    /**
     * Notifications page.
     */
    public function notifications(): void
    {
        $rows = Database::getInstance()->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 100',
            [Auth::id()]
        );
        // Mark all as read.
        Database::getInstance()->update('notifications', ['is_read' => 1], 'user_id = :uid', [':uid' => Auth::id()]);

        $this->render('messages/notifications', ['notifications' => $rows]);
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
