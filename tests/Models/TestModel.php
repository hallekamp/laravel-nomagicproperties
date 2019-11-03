<?php


namespace Hallekamp\NoMagicProperties\Test\Models;


use Hallekamp\NoMagicProperties\Models\NoMagicPropertiesModel;

class TestModel extends NoMagicPropertiesModel
{
    public $string = '';
    public $text = '';
    public $casted_data = [];
    public $integer = 0;

    protected $table = 'testmodels';

    protected $guarded = [];
    protected $fillable = [
        'string',
        'text',
        'casted_data',
        'integer',
    ];

    protected $casts = [
        'casted_data' => 'array',
    ];
}
