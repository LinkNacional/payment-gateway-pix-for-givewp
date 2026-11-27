<?php
/**
 * Caracterização das validações offline de CPF/CNPJ usadas pelo PagHiper.
 *
 * @package PGPFGForGivewp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pgpfg\PGPFGForGivewp\Includes\PGPFGivePaghiperHelper;

final class DocumentValidationTest extends TestCase
{
    public function testValidCpfIsAccepted(): void
    {
        $this->assertTrue(PGPFGivePaghiperHelper::validate_cpf('529.982.247-25'));
        $this->assertTrue(PGPFGivePaghiperHelper::validate_cpf('52998224725'));
    }

    public function testRepeatedDigitsAndWrongLengthAreRejected(): void
    {
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cpf('111.111.111-11'));
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cpf('123'));
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cpf('529.982.247-24'));
    }

    public function testValidCnpjIsAccepted(): void
    {
        $this->assertTrue(PGPFGivePaghiperHelper::validate_cnpj('11.222.333/0001-81'));
        $this->assertTrue(PGPFGivePaghiperHelper::validate_cnpj('11222333000181'));
    }

    public function testInvalidCnpjIsRejected(): void
    {
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cnpj(''));
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cnpj('11111111111111'));
        $this->assertFalse(PGPFGivePaghiperHelper::validate_cnpj('11.222.333/0001-80'));
    }
}
