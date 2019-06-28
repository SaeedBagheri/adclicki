<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable=['ppc','referer_percent','author','key_word','description','instagram_link','telegram_link','video_link','server_link','client_link','net_framework_link','access_database_link'];
}
