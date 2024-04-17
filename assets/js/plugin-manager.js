class EditorJSPluginManager {
    constructor() {
        this.tools = {};
        this.globalTunes = {};
        this.blockTunes = {}
    }

    addGlobalTune(tuneName, tune) {
        this.globalTunes[tuneName] = tune;
    }

    getGlobalTunes() {
        return this.globalTunes;
    }

    addToolTune(toolName, tuneName) {
        this.blockTunes[toolName] = this.blockTunes[toolName] || [];
        this.blockTunes[toolName].push(tuneName);
    }

    getBlockTunes(blockName) {
        return this.blockTunes[blockName];
    }

    addTool(toolName, tool) {
        this.tools[toolName] = tool;
    }

    removeTool(toolName) {
        delete this.tools[toolName];
    }

    getTools() {
        return this.tools;
    }

    getToolsWithTunes() {
        let toolsWithTunes = {};

        for (let toolName in this.tools) {
            let tool = this.tools[toolName];
            let tunes = this.getBlockTunes(toolName);

            toolsWithTunes[toolName] = {
                class: tool,
                tunes: tunes
            };
        }

        return toolsWithTunes;
    };

    createSimpleBlockTune(blockName, tuneProp, tuneLabel) {
        const simpleTune = class {
            constructor({api, data, config, block}) {
                this.api = api;
                this.data = data;
                this.config = config;
                this.block = block;
            }

            static get isTune() {
                return true;
            }

            render = () => {
                    return {
                    icon: '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M15.8 10.592v2.043h2.35v2.138H15.8v2.232h-2.25v-2.232h-2.4v-2.138h2.4v-2.28h2.25v.237h1.15-1.15zM1.9 8.455v-3.42c0-1.154.985-2.09 2.2-2.09h4.2v2.137H4.15v3.373H1.9zm0 2.137h2.25v3.325H8.3v2.138H4.1c-1.215 0-2.2-.936-2.2-2.09v-3.373zm15.05-2.137H14.7V5.082h-4.15V2.945h4.2c1.215 0 2.2.936 2.2 2.09v3.42z"/></svg>',
                    label: tuneLabel,
                    isActive: this.data,
                    toggle: true,
                    onActivate: () => {
                        this.data = !this.data;
                        this.block.dispatchChange();
                    }
                }
            }

            save = () => {
                return this.data;
            }
        }

        console.log(tuneProp, simpleTune, blockName, tuneLabel);

        this.addTool(tuneProp, simpleTune);
        this.addToolTune(blockName, tuneProp);
    }
}

// Create the plugin manager on the window object
window.editorJSPluginManager = new EditorJSPluginManager();
