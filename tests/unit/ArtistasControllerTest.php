<?php

use App\Controllers\Admin\Artistas;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ArtistasControllerTest extends CIUnitTestCase
{
    public function testTipoEtiquetaConservaElResultadoAlRefactorizar(): void
    {
        $controller = new Artistas();
        $method = new ReflectionMethod($controller, 'tipoEtiqueta');

        $this->assertSame('Solista', $method->invoke($controller, 'solista'));
        $this->assertSame('Banda', $method->invoke($controller, 'banda'));
        $this->assertSame('Otro', $method->invoke($controller, 'desconocido'));
    }

    public function testResumenRespetaElLimiteDeOchentaCaracteres(): void
    {
        $controller = new Artistas();
        $method = new ReflectionMethod($controller, 'resumirDescripcion');

        $this->assertSame('Descripción corta', $method->invoke($controller, 'Descripción corta'));
        $this->assertSame(
            str_repeat('a', 80) . '…',
            $method->invoke($controller, str_repeat('a', 81)),
        );
    }
}
