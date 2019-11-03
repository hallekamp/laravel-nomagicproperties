<?php


namespace Hallekamp\NoMagicProperties\Test;

use Hallekamp\NoMagicProperties\Test\Models\TestModel;

class ModelTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function fillAndCheckAllProperties()
    {
        $data = [
            'string' => 'teststring',
            'text' => 'long text',
            'casted_data' => [
                'bla',
            ],
            'integer' => 42,
        ];
        $model = new TestModel();
        $model->fill($data);
        $model->save();

        /** @var TestModel $dbmodel */
        $dbmodel = TestModel::find(1);
        $this->assertEquals($data['string'], $dbmodel->string);
        $this->assertEquals($data['text'], $dbmodel->text);
        $this->assertEquals($data['casted_data'], $dbmodel->casted_data);
        $this->assertEquals($data['integer'], $dbmodel->integer);
    }
}
