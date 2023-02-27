(function (Drupal, once) {
  Drupal.behaviors.farm_ui_views_table_column_preference = {
    attach: function (context, settings) {
      var wrappers = once('farm_table_column_preference', 'table[data-farm-table-column-preference]', context);
      wrappers.forEach(this.initViewsTable);
      var formTable = once('farm_table_column_preference', 'table.table-column-preference-form', context);
      formTable.forEach(this.initTableForm);
    },
    initViewsTable: function (table) {

      // Disable defaults.
      // First disable user preference, if any.
      let disabled = Drupal.behaviors.farm_ui_views_table_column_preference.getHiddenFields('farm_asset');
      console.log('here', disabled);
      if (disabled.size === 0) {
        console.log('here', disabled);
        disabled = table.dataset?.farmTableColumnPreferenceDefaultHidden?.split(' ') || [];
      }
      console.log('final', disabled);
      disabled.forEach(fieldId => Drupal.behaviors.farm_ui_views_table_column_preference.setColumnVisibility(fieldId, false));

      // Add edit button.
      var link = document.createElement('a');
      link.href = 'javascript: void(0)';
      link.classList.add('table-column-preference-link')
      link.dataset.dialogType = 'dialog';
      link.dataset.dialogRenderer = 'off_canvas';
      link.dataset.dialogOptions = '{"width": 400}';
      link.addEventListener('click', Drupal.behaviors.farm_ui_views_table_column_preference.openColumnSettingsModal);
      table.querySelector('caption').prepend(link);
    },
    openColumnSettingsModal() {
      var ajaxSettings = {
        url: '/farm/settings/table_column_preference/farm_asset',
        dialogType: 'dialog.off_canvas',
        dialogRendered: 'off_canvas',
        dialog: {
          width: '300',
        },
      };
      var myAjaxObject = Drupal.ajax(ajaxSettings);
      myAjaxObject.execute();
    },
    initTableForm(table) {

      // Add onclick callback for checkboxes.
      const formInputs = once('update_preference_callback', 'table.table-column-preference-form input[data-drupal-selector$="-enabled"]', table);
      formInputs.forEach(input => input.onclick = Drupal.behaviors.farm_ui_views_table_column_preference.columnFormToggle);

      // Update checkboxes to match existing user preferences.
      Drupal.behaviors.farm_ui_views_table_column_preference.getHiddenFields('farm_asset').forEach(field => {
        const fieldId = field.replace(/-/g, '_');
        table.querySelector(`table.table-column-preference-form input[data-field-id="${fieldId}"]`).checked = false;
      });

      // Scroll the table to the top.
      document.querySelector('table[data-farm-table-column-preference]').scrollIntoView();

    },
    columnFormToggle(event) {
      Drupal.behaviors.farm_ui_views_table_column_preference.setColumnVisibility(event.currentTarget.dataset?.fieldId, event.currentTarget.checked);
    },
    setColumnVisibility(fieldId, visible) {

      fieldId = fieldId.replace(/_/g, '-');
      const viewsClass = `views-field-${fieldId}`;
      const sheet = this.getStyleSheet();

      // Remove existing style rules for this field.
      // Rules are added and tracked as simple indexes so we must remove rules in the order they were added.
      if (visible && this.fieldStyleRuleIndex.indexOf(fieldId) > -1) {
         let index = this.fieldStyleRuleIndex.indexOf(fieldId);
         sheet.removeRule(index ? index * 2 + 1 : 0);
         sheet.removeRule(index ? index * 2: 0);
         this.fieldStyleRuleIndex.splice(index, 1);
      }
      // Add styles to hide this field's header and column cells.
      // Insert rules at the end using sheet.rules.length for convenience.
      else if(!visible){
        sheet.insertRule(`table[data-farm-table-column-preference] th.${viewsClass} { display: none; }`, sheet.rules.length);
        sheet.insertRule(`table[data-farm-table-column-preference] td.${viewsClass} { display: none; }`, sheet.rules.length);
        this.fieldStyleRuleIndex.push(fieldId);
      }

      // Update localstorage.
      this.updateHiddenField('farm_asset', fieldId, visible);
    },
    getStyleSheet() {
      // Create a style sheet if not created.
      let sheet = document.querySelector('style#table-column-preference');
      if (!sheet) {
        sheet = document.createElement('style');
        sheet.id = 'table-column-preference';
        document.head.appendChild(sheet);
      }
      return sheet.sheet;
    },
    // An array maintaining the order that styles were added.
    fieldStyleRuleIndex: [],
    getHiddenFields(view) {
      let data = this.getPreferences();
      return new Set(data[view] || []);
    },
    updateHiddenField(view, field, visible) {
      let fields = this.getHiddenFields(view);
      fields[visible ? 'delete' : 'add'](field);
      this.saveHiddenFields(view, fields);
    },
    saveHiddenFields(view, fields) {
      let data = this.getPreferences();
      data[view] = [...new Set(fields)];
      this.savePreferences(data);
    },
    getPreferences() {
      const itemName = 'Drupal.viewsTableColumnPreference';
      let data = localStorage.getItem(itemName);
      return data ? JSON.parse(data) : {};
    },
    savePreferences(data) {
      const itemName = 'Drupal.viewsTableColumnPreference';
      localStorage.setItem(itemName, JSON.stringify(data));
    },
  };
}(Drupal, once));
