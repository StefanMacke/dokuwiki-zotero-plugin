<?php

/**
 * Syntax tests for the latexit plugin
 *
 * @group plugin_zotero
 * @group plugins
 */
class syntax_plugin_zotero_test extends DokuWikiTest {

    /**
     * These plugins will be loaded for testing.
     * @var array
     */
    protected $pluginsEnabled = array('latexit', 'mathjax', 'imagereference', 'zotero');
    /**
     * Variable to store the instance of syntax plugin.
     * @var syntax_plugin_latexit_base
     */
    protected $s;

    /**
     * Prepares the testing environment.
     */
    public function setUp() {
        parent::setUp();

        $this->s = new syntax_plugin_zotero();
    }

    /**
     * Testing getType method.
     */
    public function test_getType() {
        $this->assertEquals("substition", $this->s->getType());
    }

    /**
     * Testing isSingleton method.
     */
    public function test_isSingleton() {
        $this->assertTrue($this->s->isSingleton());
    }

    /**
     * Testing handle method.
     */
    public function test_handle() {
        //test zotero part of the method
        $r = $this->s->handle("\cite{bibliography}", "", 0, new Doku_Handler());
        $this->assertEquals("bibliography", $r);
    }

    /**
     * Testing render method.
     */
    public function test_render() {
        //test recursive inserting part of method with xhtml renderer
        $r = new Doku_Renderer_xhtml();

        //test recursive inserting part of method with latex renderer
        $r = new renderer_plugin_latexit();

        //test zotero of method
        $data = "bibliography";
        $result = $this->s->render("latex", $r, $data);
        $this->assertEquals("\\cite{bibliography}", $r->doc);
        $this->assertTrue($result);
                
        //test with not implemented rendering mode
        $result = $this->s->render("doc", $r, $data);
        $this->assertFalse($result);        
    }

}
