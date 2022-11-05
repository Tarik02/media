<?php

namespace App\Enums;

enum FileType: string
{
    case STORAGE = 'disk';
    case PUBLIC = 'public';
    case YTDLP = 'ytdlp';
}
