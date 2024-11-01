<?php

namespace Tussendoor\Billink\Helpers;

class Notice
{
    protected $state;
    protected $message;
    protected $priority = 10;

    private $transientId = 'tsd_notices';

    /**
     * The contents of the notice are considered as a failed action.
     * @param  string $message
     * @return $this
     */
    public function failed($message = null)
    {
        if (!empty($message)) {
            $this->setMessage($message);
        }

        return $this->setSuccess(false);
    }

    /**
     * The contents of the notice are considered as a successful action.
     * @param  string $message
     * @return $this
     */
    public function successful($message = null)
    {
        if (!empty($message)) {
            $this->setMessage($message);
        }

        return $this->setSuccess(true);
    }

    /**
     * Wether the notice should be displayed as an success or as an error.
     * @param bool $success
     */
    public function setSuccess($success)
    {
        $this->state = $success;

        return $this;
    }

    /**
     * Set the message that's being displayed to the user.
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the priority of the notice, which sets the action priority. A higher
     * priority means the notice will be displayed lower.
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = (int) $priority;

        return $this;
    }

    /**
     * Create the notice. Stores it within the transient table,
     * as the user is redirected after a post has been saved.
     * @return bool
     */
    public function create()
    {
        $notices = $this->get();
        $messageHtml = $this->generateHtml();

        array_push($notices, $messageHtml);

        return $this->save($notices);
    }

    /**
     * Display all stored notices. If none are found, this method returns false.
     * This method deletes all stored notices after it has finised running.
     * @return bool
     */
    public function display()
    {
        $notices = $this->get();
        if (empty($notices)) {
            return false;
        }

        foreach ($notices as $notice) {
            $this->setNoticeAction($notice);
        }

        return $this->delete();
    }

    /**
     * Generate the notice HTML. Uses WordPress css classes
     * @return string
     */
    protected function generateHtml()
    {
        return sprintf('
            <div class="notice notice-%s is-dismissible">
                <p>%s</p>
            </div>
        ', $this->state ? 'success' : 'error', $this->message);
    }

    /**
     * Set the action within WordPress used for Admin notices
     * @param string $notice
     */
    protected function setNoticeAction($notice)
    {
        add_action('all_admin_notices', function () use ($notice) {
            echo $notice;
        }, $this->priority);
    }

    /**
     * Get all stored notices from the transients table.
     * @return array
     */
    private function get()
    {
        $existing = get_transient($this->transientId);

        return $existing === false ? [] : $existing;
    }

    /**
     * Save a single notice to a transient. The transient is stored for 60 seconds.
     * @param  mixed $messages
     * @return bool
     */
    private function save($messages)
    {
        return set_transient($this->transientId, $messages, 60);
    }

    /**
     * Delete all notice transients, identified by our own unique identifier.
     * @return bool
     */
    private function delete($force = false)
    {
        if ($force) {
            return delete_transient($this->transientId);
        }

        return add_action('all_admin_notices', function () {
            return delete_transient($this->transientId);
        }, $this->priority);
    }
}
