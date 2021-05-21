<?php


namespace tanmay\CallReturn;


class CallReturn
{
    private ?string $status = 'success';
    private array $error_messages = [];
    private array $success_messages = [];
    private $data = null;

    public function __construct()
    {
        $this->status = 'success';
        $this->error_messages = [];
        $this->success_messages = [];
        $this->data = null;
    }

    public function add_error($err): CallReturn
    {
        $this->status = 'error';

        //lets also clear success history :p
        $this->success_messages = [];
        $this->data = null;

        if (is_array($err)) $this->error_messages = array_merge($this->error_messages, $err);
        else $this->error_messages[] = $err;

        return $this;
    }

    public function add_success($data = null, $message = null): CallReturn
    {
        //lets also clear error history :p
        $this->error_messages = [];
        $this->data = null;

        $this->status = 'success';
        $this->data = $data;
        if ($message) {
            if (is_array($message)) $this->success_messages = array_merge($this->success_messages, $message);
            else $this->success_messages[] = $message;
        }

        return $this;
    }

    public function add_data($data): CallReturn
    {
        $this->data = $data;

        return $this;
    }

    public function clear_message(): CallReturn
    {
        $this->error_messages = [];
        $this->success_messages = [];

        return $this;
    }

    public function clear_data(): CallReturn
    {
        $this->data = null;

        return $this;
    }

    public function add_message($msg): CallReturn
    {
        if ($this->status == 'success') $this->success_messages[] = $msg;
        else $this->error_messages[] = $msg;

        return $this;
    }

    public function is_error(): bool
    {
        return ($this->status == 'error');
    }

    public function is_success(): bool
    {
        return ($this->status == 'success');
    }

    public function get_message(): array
    {
        return ($this->status == 'success' ? $this->get_success_message() : $this->get_error_message());
    }
    public function get_success_message()
    {
        return $this->success_messages;
    }
    public function get_error_message()
    {
        return $this->error_messages;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function get_in_array(): array
    {
        $ret = ['status' => '', 'error' => [], 'success' => []];
        $ret['status'] = $this->is_error() ? 'error' : 'success';
        $ret['error'] = $this->get_error_message();
        $ret['success'] = $this->get_success_message();
        $ret['data'] = $this->get_data();

        return $ret;
    }
}