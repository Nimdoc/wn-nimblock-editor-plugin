class EditorJSPluginManager {
    constructor() {
        this.plugins = {};
    }

    addPlugins(plugins) {
        this.plugins = Object.assign(this.plugins, plugins);
    }

    getPlugins() {
        return this.plugins;
    }
}

// Create the plugin manager on the window object
window.editorJSPluginManager = new EditorJSPluginManager();
