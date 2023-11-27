/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |  LICENCE
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu a vznikl díky podpoře
 * našich členů. Je určen pouze pro osobní užití a nesmí být šířen.
 */

/**
 * Editor stromového menu
 */
const editors = [];

window.onload = function () {
    CreateTreeEditor();
};

HTMLElement.prototype.destroy = function () {
    this.parentElement.removeChild(this);
};

Array.prototype.remove = function (item) {
    this.splice(this.indexOf(item), 1);
};

/**
 * Vytvoří editory okolo inputu s attributem data-tree-editor
 */
function CreateTreeEditor() {
    const elements = document.querySelectorAll("input[data-tree-editor]");
    for (let i = 0; i < elements.length; i++) {
        editors.push(new TreeEditor(elements[i]));
    }
}

/**
 * Konstruktor editoru stromové struktury
 * @param element div, ve kterém bude vytvořen editor
 * @constructor
 */
function TreeEditor(element) {
    this.output = element;
    this.lastId = 0;
    let self = this;

    this.submit = element.parentElement.parentElement.querySelector("input[type=submit]");
    this.submit.onclick = function () { // obsluha ukládání
        self.exportJson();
    };

    this.rootElement = document.createElement("div");
    this.rootElement.classList.add("tree-menu-editor-root");
    element.parentElement.appendChild(this.rootElement);

    this.root = new TreeNode(null, this, true); // kořenová položka
    this.rootElement.appendChild(this.root.element);

    // načtení dat
    const dataSource = element.getAttribute("data-source");
    if (dataSource) {
        const ajaj = new XMLHttpRequest();
        self = this;
        ajaj.open("GET", dataSource);
        ajaj.onreadystatechange = function () {
            if (ajaj.status == 200 && ajaj.readyState == 4) {
                self.loadJson(ajaj.responseText);
            } else if (ajaj.readyState == 4) { // neexistující data
                console.error("Data se nepovedlo načíst. Pravděpodobně neexistují nebo server obsluhující data nefunguje. Stavový kód odpovědi:" + ajaj.status);
                self.loadJson("[]");
            }
        };
        ajaj.send();
    }
}

/**
 * Konstruktor uzlu editoru stromové struktury
 * @param parent předek elementu
 * @param editor editor, kterému prvek náleží
 * @param root udává zdali je předek hlavní (root)
 * @constructor
 */
function TreeNode(parent, editor, root) {
    const self = this;

    this.parent = parent;
    this.editor = editor;
    this.hidden = 0;
    this.element;
    this.nodes = [];
    this.ul;
    this.prepareNodeEditor(root);

    this.urlEl;
    this.titleEl;
    this.id;
}

/**
 * Vkládá do sebe položku editoru
 * @param node
 */
TreeNode.prototype.appendNode = function (node) {
    const indexOfFirstHidden = this.indexOfFirstHidden();
    if (indexOfFirstHidden != -1) {
        this.nodes.splice(indexOfFirstHidden, 0, node);
    } else {
        this.nodes.push(node);
    }
    this.ul.appendChild(node.element);
};

TreeNode.prototype.indexOfFirstHidden = function () {
    for (let i = 0; i < this.nodes.length; i++) {
        if (this.nodes[i].element.classList.contains("tree-menu-hidden")) {
            return i;
        }
    }
    return -1;
};

/**
 * Připraví DOM editoru dané položky
 * @param root udává zdali je položka hlavní (root)
 */
TreeNode.prototype.prepareNodeEditor = function (root) {
    // vytváření ovl. prvků
    const header = document.createElement("div");
    header.classList.add("py-1");
    header.classList.add("row");
    header.classList.add("align-items-center");
    header.classList.add("justify-content-between");
    let title = document.createElement("input");
    const url = document.createElement("input");
    url.classList.add("mr-auto");
    const del = document.createElement("button");
    const addNode = document.createElement("button");

    // nastavení popisku nebo editoru titulku
    if (root) {
        title = document.createElement("label");
        title.textContent = "Editor menu";
        title.classList.add("mb-0");
        header.classList.add("ml-0");
        header.appendChild(title);
    } else {
        header.appendChild(title);
        header.appendChild(del);
        header.appendChild(url);
    }

    header.appendChild(addNode);
    title.value = "Nová kategorie";
    title.classList.add("tree-menu-title-editor");

    // tlačítko odstranit
    del.classList.add("btn-editor");
    del.classList.add("fa");
    del.classList.add("fa-trash-alt");
    del.classList.add("btn");
    del.classList.add("btn-light");
    del.classList.add("border-dark");
    del.classList.add("order-1");
    del.classList.add("ml-1");
    del.setAttribute("type", "button");
    del.node = this;
    del.onclick = this.btnRemoveMeClick;

    // tlačítko přidat
    addNode.classList.add("btn-editor");
    addNode.classList.add("fa");
    addNode.classList.add("fa-plus");
    addNode.classList.add("btn");
    addNode.classList.add("btn-light");
    addNode.classList.add("border-dark");
    addNode.setAttribute("type", "button");
    addNode.node = this;
    addNode.onclick = this.btnAddNodeClick;

    this.element = document.createElement("li");
    if (this.hidden) { // skrytí skrytých položek
        this.element.classList.add("tree-menu-hidden")
    }
    this.element.appendChild(header);
    this.titleEl = title;
    this.urlEl = url;

    this.ul = document.createElement("ul");
    this.ul.classList.add("nav");
    this.ul.classList.add("nav-list");
    this.ul.classList.add("tree");
    this.element.appendChild(this.ul);
};

/**
 * Obsluha tlačítka pro přidání položky
 */
TreeNode.prototype.btnAddNodeClick = function (e) {
    const node = new TreeNode(this.node, this.node.editor, false);
    // nastavení nového volného ID
    if (!node.id) {
        node.id = ++this.node.editor.lastId;
    }
    this.node.appendNode(node);
    e.preventDefault();
};

/**
 * Obsluha odstranění položky
 */
TreeNode.prototype.btnRemoveMeClick = function () {
    this.node.parent.nodes.remove(this.node);
    this.parentElement.parentElement.destroy();
};

/**
 * Vyhledá a vrátí položku podle ID i v podúrovních
 * @param id
 * @returns TreeNode
 */
TreeNode.prototype.findNode = function (id) {
    if (!id) {
        return this;
    }
    if (this.id == id) {
        return this;
    }

    for (let i = 0; i < this.nodes.length; i++) {
        const found = this.nodes[i].findNode(id);
        if (found) {
            return found;
        }
    }

    return null;
};

/**
 * Načte do editoru data ve formátu JSON
 * @param rawData
 */
TreeEditor.prototype.loadJson = function (rawData) {
    const obj = JSON.parse(rawData);

    for (let i = 0; i < obj.length; i++) {
        const parent = this.root.findNode(obj[i].parent_category_id);
        const node = new TreeNode(parent, this, false);
        node.titleEl.value = obj[i].title;
        node.urlEl.value = obj[i].url;
        node.hidden = obj[i].hidden;
        node.id = obj[i].category_id;
        if (node.hidden){
            node.element.classList.add("tree-menu-hidden");
        }
        if (this.lastId < obj[i].category_id) {
            this.lastId = obj[i].category_id;
        }
        parent.appendNode(node);
    }
};

/**
 * Vytvoří JSON položky
 * @param output kolekce, do které má přidat položka přidat sebe a rekurzivně zavolat to stejné na podpoložkách
 */
TreeNode.prototype.getJson = function (output) {
    const obj = {
        category_id: this.id,
        title: this.titleEl.value,
        url: this.urlEl.value,
        parent_category_id: this.parent ? this.parent.id : null,
        order_no: output.length + 1,
        hidden: this.hidden
    };
    output.push(obj);
    for (let i = 0; i < this.nodes.length; i++) {
        this.nodes[i].getJson(output);
    }
};

/**
 * Vygeneruje JSON a uloží jej do políčka input ve formuláři
 */
TreeEditor.prototype.exportJson = function () {
    const output = [];
    this.root.getJson(output);
    output.shift();
    this.output.value = JSON.stringify(output);
};