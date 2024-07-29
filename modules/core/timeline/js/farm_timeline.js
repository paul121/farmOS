(function (Drupal, drupalSettings, once, farmOS) {
  Drupal.behaviors.farm_timeline_gantt = {
    attach: function (context, settings) {
      once('timelineGantt', '.farm-timeline', context).forEach(function (element) {
        const opts = {
          props: {
            taskElementHook: (node, task) => {
              let popup;

              function onHover() {
                popup = createPopup(task, node);
              }

              function onLeave() {
                if (popup) {
                  popup.remove();
                }
              }

              node.addEventListener('mouseenter', onHover);
              node.addEventListener('mouseleave', onLeave);
              return {
                destroy() {
                  node.removeEventListener('mouseenter', onHover);
                  node.removeEventListener('mouseleave', onLeave);
                }
              }
            }
          },
        };

        // Create the timeline instance.
        const timeline = farmOS.timeline.create(element, opts);

        // Process timeline rows.
        const timelineRows = JSON.parse(element.dataset?.timelineRows) ?? [];
        timelineRows.forEach((row) => this.processRow);

        function createPopup(task, node) {
          const rect = node.getBoundingClientRect();
          const div = document.createElement('div');
          div.className = 'sg-popup';
          div.innerHTML = `
            <div class="sg-popup-title">${task.label}</div>
            <div class="sg-popup-item">From: ${new Date(task.from).toLocaleDateString()}</div>
            <div class="sg-popup-item">To: ${new Date(task.to).toLocaleDateString()}</div>
        `;
          div.style.position = 'absolute';
          div.style.top = `${rect.bottom + window.scrollY + 5}px`;
          div.style.left = `${rect.left + rect.width / 2}px`;

          if (task?.meta?.entity_type === 'log') {
            div.innerHTML = `
            <div class="sg-popup-title">Log: ${task.meta.label}</div>
            <div class="sg-popup-item">Type: ${task.meta.entity_bundle}</div>
            <div class="sg-popup-item">Timestamp: ${new Date(task.from).toLocaleDateString()}</div>
        `;
          }

          if (task?.meta?.stage) {
            div.innerHTML = `
            <div class="sg-popup-title">Stage: ${task.meta.stage}</div>
            <div class="sg-popup-item">From: ${new Date(task.from).toLocaleDateString()}</div>
            <div class="sg-popup-item">To: ${new Date(task.to).toLocaleDateString()}</div>
        `;
          }

          document.body.appendChild(div);
          return div;
        }

        // Open entity page on click.
        timeline.timeline.api.tasks.on.select((task) => {
          task = task[0];
          if (task.model?.editUrl) {
            var ajaxSettings = {
              url: task.model.editUrl,
              dialogType: 'dialog',
              dialogRenderer: 'off_canvas',
            };
            var myAjaxObject = Drupal.ajax(ajaxSettings);
            myAjaxObject.execute();
          } else {
            let dialog = document.getElementById('drupal-off-canvas');
            if (dialog) {
              Drupal.dialog(dialog, {}).close();
            }
          }
        });
      });
    },
    // Helper function to process a row provided to the timeline element.
    // Rows may be objects or URL strings to request dynamic row data.
    processRow: function(row) {
      if (typeof row === "object") {
        this.processRowData(row);
      }
      else if (typeof row === "string") {
        const response = fetch(row)
          .then(res => res.json())
          .then(data => {
            for (let i in data.rows) {
              const {row, tasks} = this.processRowData(data.rows[i]);
              if (row) {
                timeline.addRows([row]);
              }
              if (tasks) {
                timeline.addTasks(tasks);
              }
            }
          });
      }
    },
    // Helper function to process a row data object.
    // Collect tasks and child rows and child tasks.
    processRowData: function(row) {
      // Map to a row object.
      let mappedRow = this.mapRow(row);

      // Collect all tasks for the row.
      let tasks = row?.tasks?.map(this.mapTask) ?? [];

      // Process children rows.
      // Only create the children array if there are child rows.
      let processedChildren = row?.children?.map(this.processRow) ?? [];
      if (processedChildren.length) {
        mappedRow.children = [];
        processedChildren.forEach((child) => {
          mappedRow.children.push(child.row);
          tasks.push(...child.tasks)
        });
      }
      return {row: mappedRow, tasks};
    },
    // Helper function to map row properties.
    mapRow: function(row) {
      return {
        id: row.id,
        label: row.label,
        headerHtml: row.link,
        expanded: row.expanded,
      };
    },
    // Helper function to map task properties.
    mapTask: function(task) {
      return {
        id: task.id,
        resourceId: task.resource_id,
        from: task.start,
        to: task.end,
        label: task.label,
        editUrl: task.edit_url,
        enableDragging: task.enable_dragging,
        meta: task?.meta,
        classes: task.classes,
      };
    },
  };
}(Drupal, drupalSettings, once, farmOS));
