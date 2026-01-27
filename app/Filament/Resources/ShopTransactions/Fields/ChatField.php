<?php

namespace App\Filament\Resources\ShopTransactions\Fields;

use Filament\Forms\Components\Field;

class ChatField extends Field
{
    protected string $view = 'filament.forms.components.fields.chat-message';

    protected string $type = 'chat';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);
    }
}
