<?php

namespace Tests\Unit;

use App\Support\LocalSpecialistCopy;
use PHPUnit\Framework\TestCase;

class LocalSpecialistCopyTest extends TestCase
{
    public function test_expert_labels_become_local_specialist_without_repeated_local(): void
    {
        $this->assertSame('Local Specialist', LocalSpecialistCopy::replace('Expert'));
        $this->assertSame('Afro-Vertex Local Specialists', LocalSpecialistCopy::replace('Afro-Vertex Safari Experts'));
        $this->assertSame('our local specialists', LocalSpecialistCopy::replace('our local travel experts'));
        $this->assertSame('Plan With Local Specialists', LocalSpecialistCopy::replace('Plan With Local Experts'));
    }

    public function test_html_attributes_links_and_unrelated_words_are_preserved(): void
    {
        $this->assertSame('<a href="/experts" class="expert">Local Specialist</a>', LocalSpecialistCopy::replace('<a href="/experts" class="expert">Expert</a>'));
        $this->assertSame('Our expertise', LocalSpecialistCopy::replace('Our expertise'));
    }
}
