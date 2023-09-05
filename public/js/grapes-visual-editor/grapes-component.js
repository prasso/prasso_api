
// Editor imports
import { initEditor } from './editor.js';
import { bindEditorEvents } from './events.js';

// Init editor
const editor = initEditor();

// Bind events
bindEditorEvents(editor);


  // Fetch the combined HTML and set it as the initial content of the editor
  jQuery.ajax({
    url: '/visual-editor/getCombinedHtml/'+page_id,
    type: 'GET',
    success: function(response) {
      editor.setComponents(response.html);
    },
    error: function(response) {
      console.log('Error fetching combined HTML');
    },
  });