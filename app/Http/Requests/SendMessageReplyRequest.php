<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageReplyRequest extends FormRequest
{
    /**
     * Authorized via the MessagePolicy@reply ability.
     */
    public function authorize(): bool
    {
        $message = $this->route('message');

        return $message instanceof Message
            && $this->user('admin')?->can('reply', $message);
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:191'],
            'reply_message' => ['required', 'string', 'min:2', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reply_message' => 'reply message',
        ];
    }
}
