<?php

namespace Elearning\Util\Menu;

use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\ITranslator;

/**
 * Strom menu
 *
 * @author stopka
 */
class Menu extends \Nette\Application\UI\Control {

    /** @var \Nette\ */
    protected $translator;

    /** @var \Nette\Security\User */
    protected $user;

    /** @var \string|callable */
    protected $link;

    /** @var array */
    protected $linkArgs;

    /** @var callable|string */
    protected $title;

    /** @var \bool */
    protected $active = false;

    /** @var \bool */
    protected $inPath = false;

    /** @var \bool */
    protected $currentable = true;

    /** @var  \string */
    protected $icon;

    /** @var callback|bool */
    private $show = TRUE;

    /** @var  mixed */
    protected $idParam;

    /** @var  null|\string */
    protected $idNeeded;

    /** @var null|array */
    protected $authorization = NULL;

    /** @var callback|string */
    protected $class;

    /** @var bool */
    protected $beforeRenderCalled = false;

    /**
     * Menu item constructor.
     * @param ITranslator $translator
     * @param string|callable $link url, nette link or callback
     * @param array $linkArgs
     * @param string $title
     */
    public function __construct($translator, $title, $link, array $linkArgs = []) {
        parent::__construct();
        $this->translator = $translator;
        $this->link = $link;
        $this->linkArgs = $linkArgs;
        $this->title = $title;
    }

    /**
     * Generates original subcomponent name automatically
     * @return string
     */
    private function generateSubcomponentName(): string {
        $name = 'menu';
        $trial = $name;
        $count = 1;
        while ($this->getComponent($trial, false) != null) {
            $trial = $name . '_' . $count;
            $count++;
        }
        return $trial;
    }

    /**
     * Adds next menu item as a child
     * @param \string $link
     * @param array $linkArgs
     * @param \string $title
     * @param \string $name
     * @return Menu
     */
    public function add($link, $linkArgs, $title, $name = NULL): Menu {
        if ($name === NULL) {
            $name = $this->generateSubcomponentName();
        }
        $result = new Menu($this->translator, $link, $linkArgs, $title);
        $this->addComponent($result, $name);
        return $result;
    }

    /**
     * Creates instance of class and adds it to the tree
     * @param \string $class class name
     * @param \string $name
     * @return Menu
     * @throws \Exception
     */
    public function addMenu($class, $name) {
        if (!is_subclass_of($class, Menu::class)) {
            throw new MenuException("$class is not subclass of Menu");
        }
        $result = new $class($this->translator, $this, $name);
        return $result;
    }

    public function render() {
        $this->renderTree();
    }

    public function renderItem(bool $showlink = true) {
        $this->callBeforeRender();
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/Item.latte');
        $template->node = $this;
        $template->help = $this->help;
        $template->showlink = $showlink;
        $template->render();
    }

    /**
     * @param null $root_name
     * @throws MenuException
     */
    public function renderTree(string $root_name = null) {
        $this->callBeforeRender();
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/Tree.latte');
        if ($root_name == NULL) {
            $template->node = $this;
        } else {
            /** @var Menu $node */
            $node = $this->getComponent($root_name, false);
            if (!$node) {
                $node = $this->getDeepMenuComponent($root_name);
            }
            $template->node = $node;
        }
        $template->render();
    }

    public function renderSubtree() {
        $this->callBeforeRender();
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/Subtree.latte');
        $template->node = $this;
        $template->render();
    }

    public function renderPath() {
        $this->callBeforeRender();
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/Path.latte');
        $template->path = $this->getCurrentPath();
        $template->render();
    }

    /**
     * @param string|NULL $node_name
     * @throws MenuException
     */
    public function renderChildren(string $node_name = NULL) {
        $this->callBeforeRender();
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/Children.latte');
        /** @var Menu $node */
        $node = null;
        if ($node_name == NULL) {
            $node = $this;
        } else {
            $node = $this->getDeepMenuComponent($node_name);
        }
        $template->node = $node;
        $template->render();
    }

    /**
     * @param $name
     * @return Menu
     * @throws MenuException
     */
    public function getDeepMenuComponent($name): Menu {
        $all = $this->getComponents(true, Menu::class);
        foreach ($all as $one) {
            if ($one->getName() == $name) {
                return $one;
            }
        }
        throw new MenuException("Component not found");
    }

    /**
     * Is link url string?
     * @return bool
     */
    public function hasDirectUrl(): bool {
        if (is_string($this->link) && (substr($this->link, 0, 7) === "http://" || substr($this->link, 0, 8) === "https://")) {
            return true;
        }
        return false;
    }

    /**
     * Vrací vygenerovanou URL
     * @return null|string
     * @throws InvalidLinkException
     */
    public function getUrl(): ?string {
        if (is_callable($this->link)) {
            return call_user_func($this->link);
        }
        if ($this->hasDirectUrl()) {
            return $this->link;
        }
        if ($this->idNeeded !== null) {
            if ($this->idParam == null) {
                return null;
            }
            return $this->getPresenter()->link($this->link, $this->idParam);
        }
        return $this->getPresenter()->link($this->link);
    }

    /**
     * Returns translated title
     * @return \string
     */
    public function getTitle() {
        if ($this->translator) {
            return $this->title;
        }
        return $this->translator->translate($this->title);
    }

    /**
     *
     * @param ITranslator|FALSE $translator
     */
    public function setTranslator($translator) {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Children of current menu item
     * @param bool $deep
     * @return \Iterator
     */
    public function getChildren($deep = FALSE) {
        return $this->getComponents($deep, Menu::class);
    }

    /**
     * Sets if item is visible in menu
     * @param callback|string $show
     * @return Menu
     */
    public function setShow($show) {
        $this->show = $show;

        return $this;
    }

    /**
     * Checks if item is visible (even by controling permissions)
     * @return \bool
     */
    public function getShow(): bool {
        if (!$this->isAllowed()) {
            return FALSE;
        }
        if (is_callable($this->show)) {
            return (boolean)call_user_func($this->show, $this);
        }
        return $this->show;
    }

    /**
     * Pro funkčnost odkazu je nutné nastavit parametr id
     * @param \string $key identifikátor typu id
     * @param null|mixed $default výchozí hodnota
     * @return $this
     */
    public function setIdNeeded($key, $default = null) {
        $this->idNeeded = $key;
        $this->idParam = $default;
        return $this;
    }

    /**
     * Sets html class
     * @param callback|string $class
     * @return Menu
     */
    public function setClass($class) {
        $this->class = $class;
        return $this;
    }

    /**
     * Sets icon
     * @param \string $class
     * @return Menu $this
     */
    public function setIcon($class) {
        $this->icon = $class;
        return $this;
    }

    public function getIcon() {
        return $this->icon;
    }

    /**
     * Vrátí css třídu položky
     * @return \string
     */
    public function getClass() {
        if (is_callable($this->class)) {
            return call_user_func($this->class, $this);
        }
        return $this->class;
    }

    public function isActive() {
        return $this->active;
    }

    /**
     * Zda je položka vypisovatelná a aktivní
     * @return bool
     */
    public function isCurrent() {
        return ($this->currentable && $this->isActive());
    }

    /**
     * Nastaví příznak, zda může být použit v cestě jako aktivní
     * @param bool $bool
     * @return Menu
     */
    public function setCurentable($bool = true) {
        $this->currentable = $bool;
        return $this;
    }

    /**
     * Vrátí cestu Menu prvků od tohoto prvku ke kořeni
     * @return Menu[]
     */
    public function getPath() {
        $path = Array($this);
        if ($this->parent instanceof Menu) {
            $path = array_merge($this->parent->getPath(), $path);
        }
        return $path;
    }

    /**
     * Najde v podstromu aktivní prvky
     * @return Menu[]
     */
    public function findCurrent() {
        return $this->find(function ($node) {
            return $node->isCurrent();
        });
    }

    /**
     * Najde v podstromu prvky odpovídající vyhodnocovacímu callbacku
     * @param callback $check
     * @return Menu[]
     */
    protected function find($check) {
        $result = Array();
        foreach ($this->getChildren(TRUE) as $child) {
            if ((boolean)call_user_func($check, $child)) {
                $result[] = $child;
            }
        }
        return $result;
    }

    /**
     * Vrátí cestu prvků k prvnímu aktivnímu prvku
     * @return Menu[]
     */
    public function getCurrentPath() {
        $node = $this->findCurrent();
        if (count($node) == 0) {
            return Array($this);
        }
        return $node[0]->getPath();
    }

    /**
     * Nastaví příznak aktivní položky
     * @param \bool $value
     */
    public function setActive($value = true) {
        $this->active = $value;
    }

    /**
     * Nastaví příznak položky obsažené v cestě
     * Příznak se nastaví i na rodičovi
     * @param bool $value
     * @param bool|int $set_parent je-li číslené, určiuje do jaké úrovně rodičů se má hodnota nastavit
     */
    public function setInPath($value = true, $set_parent = true) {
        $this->inPath = $value;
        if ($set_parent) {
            /** @var Menu $parent */
            $parent = $this->getParent();
            if (is_a($parent, "Elearning\Util\Menu\Menu")) {
                $set_parent = is_int($set_parent) ? $set_parent - 1 : true;
                $parent->setInPath($value, $set_parent);
            }
        }
    }

    /**
     * Vrátí zda je nastaven příznak, že je položka v cestě
     * @param bool $currentable_only zda se má počítat jen je-li položka currentable
     * @return bool
     */
    public function isInPath($currentable_only = false) {
        if ($currentable_only && !$this->currentable) {
            return false;
        }
        return $this->inPath;
    }

    /**
     * Nastaví autamaticky příznak aktivní položky a cesty v podstromu menu
     */
    public function setActiveByPresenter() {
        if ($this->hasDirectUrl()) {
            $this->setActive(false);
        } else {
            $is_current_link = $this->getPresenter()->isLinkCurrent($this->link);
            $this->setActive($is_current_link);
            if ($is_current_link) {
                $this->setInPath($is_current_link);
            }
        }
        foreach ($this->getChildren() as $child) {
            $child->setActiveByPresenter();
        }
    }

    /**
     * Nastaví autamaticky id potřebným položkám
     */
    public function setIdParameter($key, $value) {
        if ($this->idNeeded == $key) {
            $this->idParam = $value;
        }
        foreach ($this->getChildren() as $child) {
            $child->setIdParameter($key, $value);
        }
    }

    /**
     * Nastaví práva k zobrazení této položky
     * @param \string|\string[]|\NULL,FALSE $resource false znamená žádnou kontrolu
     * @param \string|NULL $privilege
     */
    public function setAuthorization($resource = NULL, $privilege = NULL) {
        if ($resource === false) {
            $this->authorization = null;
        }
        $this->authorization = [$resource, $privilege];
        return $this;
    }


    /**
     * Zjistí zda má uživatel právo vidět položku
     * @return \bool
     */
    public function isAllowed() {
        if ($this->authorization == NULL) {
            return TRUE;
        }
        list($resource, $privilege) = $this->authorization;
        return $this->getPresenter()->getUser()->isAllowed($resource, $privilege);
    }

    protected function callBeforeRender() {
        if ($this->beforeRenderCalled) {
            return;
        }
        $this->beforeRenderCalled = true;
        $this->beforeRender();
    }

    /**
     * Voláno před renderováním komponenty
     */
    protected function beforeRender() {

    }

}


