<?php

use App\Models\ArtistaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ArtistaModelTest extends CIUnitTestCase
{
    public function testDefinesExpectedFieldsAndValidationRules(): void
    {
        $modelClass = new ReflectionClass(ArtistaModel::class);
        $model = $modelClass->newInstanceWithoutConstructor();

        $this->assertSame([
            'nombre_artistico',
            'nombre_real',
            'tipo',
            'genero',
            'pais',
            'descripcion',
            'imagen',
            'estado',
        ], $modelClass->getProperty('allowedFields')->getValue($model));

        $rules = $modelClass->getProperty('validationRules')->getValue($model);

        $this->assertArrayHasKey('nombre_artistico', $rules);
        $this->assertArrayHasKey('tipo', $rules);
        $this->assertArrayHasKey('estado', $rules);
        $this->assertStringContainsString('in_list[solista,banda,duo,orquesta,otro]', $rules['tipo']);
        $this->assertStringContainsString('in_list[activo,inactivo]', $rules['estado']);
    }
}
