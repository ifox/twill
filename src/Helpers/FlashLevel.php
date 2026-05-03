<?php

namespace A17\Twill\Helpers;

enum FlashLevel: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'caution';
    case INFO = 'help';
}
