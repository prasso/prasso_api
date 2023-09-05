// Editor initialization
export function bindEditorEvents( editor ) {
    var pn = editor.Panels;
    var modal = editor.Modal;
    var cmdm = editor.Commands;
    const dc = editor.DomComponents;

    // Update canvas-clear command
    cmdm.add('canvas-clear', function () {
        if (confirm('Are you sure to clean the canvas?')) {
            editor.runCommand('core:canvas-clear')
            setTimeout(function () { localStorage.clear() }, 0)
        }
    });

    // editor panel not readonly
    var pfx = editor.getConfig().stylePrefix;
    var codeViewer = editor.CodeManager.getViewer('CodeMirror').clone();
    var container = document.createElement('div');
    var btnEdit = document.createElement('button');

    codeViewer.set({
        codeName: 'htmlmixed',
        readOnly: 0,
        theme: 'hopscotch',
        autoBeautify: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        lineWrapping: true,
        styleActiveLine: true,
        smartIndent: true,
        indentWithTabs: true
    });

    btnEdit.innerHTML = 'Edit Code';
    btnEdit.className = pfx + 'btn-prim ' + pfx + 'btn-import';
    btnEdit.onclick = function () {
        var code = codeViewer.editor.getValue();
        editor.DomComponents.getWrapper().set('content', '');
        editor.setComponents(code.trim());
        modal.close();
    };

    cmdm.add('html-edit', {
        run: function (editor, sender) {
            sender && sender.set('active', 0);
            var viewer = codeViewer.editor;
            modal.setTitle('Edit code');
            if (!viewer) {
                var txtarea = document.createElement('textarea');
                container.appendChild(txtarea);
                container.appendChild(btnEdit);
                codeViewer.init(txtarea);
                viewer = codeViewer.editor;
            }
            var InnerHtml = editor.getHtml();
            var Css = editor.getCss();
            modal.setContent('');
            modal.setContent(container);
            codeViewer.setContent(InnerHtml + "<style>" + Css + '</style>');
            modal.open();
            viewer.refresh();
        }
    });

    pn.addButton('options',
        [
            {
                id: 'edit',
                className: 'fa fa-edit',
                command: 'html-edit',
                attributes: {
                    'title': 'Code Editor',
                    'data-tooltip-pos': 'bottom',
                }
            }
        ]
    );

    pn.addButton('options', [{
        id: 'save',
        className: 'fa fa-floppy-o icon-blank', command: function (editor1, sender) {
            var htmlString = editor1.getHtml();
            const parser = new DOMParser();
            const htmlDoc = parser.parseFromString(htmlString, 'text/html');

            const coreDiv = htmlDoc.querySelector('#core');
            const coreContents = coreDiv.innerHTML;
            //need just the the html inside div id="core"
            if (coreContents != null) {
                document.getElementById("page_data").value = coreContents;
            }
            var cssRules = editor.getCss();
            console.log(cssRules);
            if (cssRules != null) {
                document.getElementById("page_style").value = cssRules;
            }
            var spf = document.getElementById("sitePageForm")
            spf.submit();

        }, attributes: {
            'title': 'Save Page',
            'data-tooltip-pos': 'bottom',
        }
    },]);

    pn.addButton('options', {
        id: 'dashboard',
        className: 'fa fa-home',
        command: function (editor1, sender) {
            if (userIsAdmin) {
                window.location.replace("/sites");}
            else
               {} window.location.replace("/site/edit");
            
        },
        attributes: {
            'title': 'Return to Home',
            'data-tooltip-pos': 'bottom',
        }
    });

    // Add info command
    var mdlClass = 'gjs-mdl-dialog-sm';
    var infoContainer = document.getElementById('info-panel');

    cmdm.add('open-info', function () {
        var mdlDialog = document.querySelector('.gjs-mdl-dialog');
        mdlDialog.className += ' ' + mdlClass;
        infoContainer.style.display = 'block';
        modal.setTitle('About this editor');
        modal.setContent(infoContainer);
        modal.open();
        modal.getModel().once('change:open', function () {
            mdlDialog.className = mdlDialog.className.replace(mdlClass, '');
        })
    });

    pn.addButton('options', {
        id: 'open-info',
        className: 'fa fa-question-circle',
        command: function () { editor.runCommand('open-info') },
        attributes: {
            'title': 'About',
            'data-tooltip-pos': 'bottom',
        }
    });

    // Simple warn notifier
    var origWarn = console.warn;
    toastr.options = {
        closeButton: true,
        preventDuplicates: true,
        showDuration: 250,
        hideDuration: 150
    };
    console.warn = function (msg) {
        if (msg.indexOf('[undefined]') == -1) {
            toastr.warning(msg);
        }
        origWarn(msg);
    };


    // Add and beautify tooltips
    [['sw-visibility', 'Show Borders'], ['preview', 'Preview'], ['fullscreen', 'Fullscreen'],
    ['export-template', 'Export'], ['undo', 'Undo'], ['redo', 'Redo'],
    ['gjs-open-import-webpage', 'Import'], ['canvas-clear', 'Clear canvas']]
        .forEach(function (item) {
            pn.getButton('options', item[0]).set('attributes', { title: item[1], 'data-tooltip-pos': 'bottom' });
        });
    [['open-sm', 'Style Manager'], ['open-layers', 'Layers'], ['open-blocks', 'Blocks']]
        .forEach(function (item) {
            pn.getButton('views', item[0]).set('attributes', { title: item[1], 'data-tooltip-pos': 'bottom' });
        });
    var titles = document.querySelectorAll('*[title]');

    for (var i = 0; i < titles.length; i++) {
        var el = titles[i];
        var title = el.getAttribute('title');
        title = title ? title.trim() : '';
        if (!title)
            break;
        el.setAttribute('data-tooltip', title);
        el.setAttribute('title', '');
    }


    // Store and load events
    editor.on('storage:load', function (e) { console.log('Loaded ', e) });
    editor.on('storage:store', function (e) { console.log('Stored ', e) });


    // Do stuff on load
    editor.on('load', function () {
        var $ = grapesjs.$;

        // Show borders by default
        pn.getButton('options', 'sw-visibility').set('active', 1);

        // Load and show settings and style manager
        var openTmBtn = pn.getButton('views', 'open-tm');
        openTmBtn && openTmBtn.set('active', 1);
        var openSm = pn.getButton('views', 'open-sm');
        openSm && openSm.set('active', 1);

        // Remove trait view
        pn.removeButton('views', 'open-tm');

        // Add Settings Sector
        var traitsSector = $('<div class="gjs-sm-sector no-select">' +
            '<div class="gjs-sm-sector-title"><span class="icon-settings fa fa-cog"></span> <span class="gjs-sm-sector-label">Settings</span></div>' +
            '<div class="gjs-sm-properties" style="display: none;"></div></div>');
        var traitsProps = traitsSector.find('.gjs-sm-properties');
        traitsProps.append($('.gjs-trt-traits'));
        $('.gjs-sm-sectors').before(traitsSector);
        traitsSector.find('.gjs-sm-sector-title').on('click', function () {
            var traitStyle = traitsProps.get(0).style;
            var hidden = traitStyle.display == 'none';
            if (hidden) {
                traitStyle.display = 'block';
            } else {
                traitStyle.display = 'none';
            }
        });

        // Open block manager
        var openBlocksBtn = pn.getButton('views', 'open-blocks');
        openBlocksBtn && openBlocksBtn.set('active', 1);


        //object toolbar menu mods
        const id = 'custom-id';
        const htmlLabel = `<svg viewBox="0 0 24 24">
                <path d="M14.6 16.6l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4m-5.2 0L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4z"></path>
              </svg>`;

        dc.getTypes().forEach(elType => {
            let { model: oldModel, view: oldView } = elType;
            console.log(JSON.stringify(elType));
            dc.addType(elType.id, {
                model: oldModel.extend({
                    initToolbar() {
                        oldModel.prototype.initToolbar.apply(this, arguments);
                        const toolbar = this.get('toolbar');

                        if (!toolbar.filter(tlb => tlb.id === id).length) {
                            toolbar.unshift({
                                id,
                                command: function () { alert("Custom toolbar"); },
                                label: htmlLabel
                            });
                            this.set('toolbar', toolbar);
                        }
                    }
                }),
                view: oldView
            });
        });


    });

    return editor;
}