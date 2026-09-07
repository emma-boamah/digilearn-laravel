/**
 * DigiLearn Interactive Collaborative Whiteboard Engine
 * Built with Fabric.js, KaTeX math rendering, and PDF.js slide loading.
 */

export class VirtualWhiteboard {
    constructor(canvasId, options = {}) {
        this.canvasId = canvasId;
        this.roomId = options.roomId || '';
        this.userId = options.userId || null;
        this.userName = options.userName || 'User';
        this.isTutor = options.isTutor || false;
        this.onSync = options.onSync || null;

        // Current tool settings
        this.currentTool = 'pen'; // 'select', 'pen', 'highlighter', 'rect', 'circle', 'line', 'arrow', 'text', 'eraser'
        this.currentColor = '#2677B8'; // Default DigiLearn blue
        this.currentWidth = 3;
        this.isDrawing = false;

        // History for Undo/Redo
        this.history = [];
        this.historyIndex = -1;
        this.isStateChanging = false;

        // Multi-page slides (PDF)
        this.currentPage = 1;
        this.totalPages = 1;
        this.pdfDoc = null;

        this.initCanvas();
    }

    initCanvas() {
        const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);
        if (!fb) {
            console.warn('Fabric.js not loaded yet, retrying canvas init in 100ms...');
            setTimeout(() => this.initCanvas(), 100);
            return;
        }

        const container = document.getElementById('whiteboard-container');
        const width = container && container.clientWidth ? container.clientWidth : 1000;
        const height = container && container.clientHeight ? container.clientHeight : 650;

        const canvasEl = document.getElementById(this.canvasId);
        if (!canvasEl) {
            console.warn(`Canvas element #${this.canvasId} not found in DOM yet.`);
            return;
        }

        this.canvas = new fb.Canvas(this.canvasId, {
            width: width,
            height: height,
            isDrawingMode: true,
            backgroundColor: '#ffffff',
            selection: true,
            preserveObjectStacking: true,
        });

        // Configure default freehand brush
        this.setupBrush();

        // Canvas event listeners
        this.setupEventListeners();

        // Responsive resize
        window.addEventListener('resize', () => this.resizeCanvas());

        // Save initial blank state
        this.saveState();
    }

    setupBrush() {
        const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);
        if (!this.canvas || !fb) return;
        this.canvas.freeDrawingBrush = new fb.PencilBrush(this.canvas);
        this.canvas.freeDrawingBrush.color = this.currentColor;
        this.canvas.freeDrawingBrush.width = parseInt(this.currentWidth, 10);
    }

    setupEventListeners() {
        // Broadcast stroke when path is created
        this.canvas.on('path:created', (e) => {
            this.saveState();
            if (this.onSync && !this.isReceivingRemote) {
                this.onSync('add-object', {
                    object: e.path.toObject(['id', 'name']),
                    page: this.currentPage,
                });
            }
        });

        this.canvas.on('object:modified', (e) => {
            this.saveState();
            if (this.onSync && !this.isReceivingRemote) {
                this.onSync('modify-object', {
                    object: e.target.toObject(['id', 'name']),
                    page: this.currentPage,
                });
            }
        });

        this.canvas.on('mouse:down', (e) => this.handleMouseDown(e));
        this.canvas.on('mouse:move', (e) => this.handleMouseMove(e));
        this.canvas.on('mouse:up', (e) => this.handleMouseUp(e));
    }

    setTool(tool) {
        this.currentTool = tool;
        this.canvas.isDrawingMode = false;
        this.canvas.selection = false;

        if (tool === 'select') {
            this.canvas.selection = true;
            this.canvas.defaultCursor = 'default';
        } else if (tool === 'pen') {
            this.canvas.isDrawingMode = true;
            this.canvas.freeDrawingBrush = new fabric.PencilBrush(this.canvas);
            this.canvas.freeDrawingBrush.color = this.currentColor;
            this.canvas.freeDrawingBrush.width = parseInt(this.currentWidth, 10);
        } else if (tool === 'highlighter') {
            this.canvas.isDrawingMode = true;
            this.canvas.freeDrawingBrush = new fabric.PencilBrush(this.canvas);
            // Semi-transparent RGBA color
            const rgba = this.hexToRgba(this.currentColor, 0.35);
            this.canvas.freeDrawingBrush.color = rgba;
            this.canvas.freeDrawingBrush.width = parseInt(this.currentWidth, 10) * 4;
        } else if (tool === 'eraser') {
            this.canvas.defaultCursor = 'crosshair';
        } else {
            this.canvas.defaultCursor = 'crosshair';
        }
    }

    setColor(color) {
        this.currentColor = color;
        if (this.canvas.freeDrawingBrush) {
            if (this.currentTool === 'highlighter') {
                this.canvas.freeDrawingBrush.color = this.hexToRgba(color, 0.35);
            } else {
                this.canvas.freeDrawingBrush.color = color;
            }
        }
    }

    setWidth(width) {
        this.currentWidth = width;
        if (this.canvas.freeDrawingBrush) {
            const mult = this.currentTool === 'highlighter' ? 4 : 1;
            this.canvas.freeDrawingBrush.width = parseInt(width, 10) * mult;
        }
    }

    handleMouseDown(e) {
        if (['select', 'pen', 'highlighter'].includes(this.currentTool)) return;

        const pointer = this.canvas.getPointer(e.e);
        this.origX = pointer.x;
        this.origY = pointer.y;
        this.isDrawing = true;

        const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);
        if (!fb || !this.canvas) return;

        if (this.currentTool === 'rect') {
            this.activeShape = new fb.Rect({
                left: this.origX,
                top: this.origY,
                originX: 'left',
                originY: 'top',
                width: 0,
                height: 0,
                fill: 'transparent',
                stroke: this.currentColor,
                strokeWidth: parseInt(this.currentWidth, 10),
                selectable: true,
            });
            this.canvas.add(this.activeShape);
        } else if (this.currentTool === 'circle') {
            this.activeShape = new fb.Ellipse({
                left: this.origX,
                top: this.origY,
                originX: 'left',
                originY: 'top',
                rx: 0,
                ry: 0,
                fill: 'transparent',
                stroke: this.currentColor,
                strokeWidth: parseInt(this.currentWidth, 10),
                selectable: true,
            });
            this.canvas.add(this.activeShape);
        } else if (this.currentTool === 'line') {
            const points = [this.origX, this.origY, this.origX, this.origY];
            this.activeShape = new fb.Line(points, {
                strokeWidth: parseInt(this.currentWidth, 10),
                stroke: this.currentColor,
                selectable: true,
            });
            this.canvas.add(this.activeShape);
        } else if (this.currentTool === 'text') {
            const text = new fb.IText('Type lesson notes here...', {
                left: this.origX,
                top: this.origY,
                fontFamily: 'Inter, sans-serif',
                fontSize: 18,
                fill: this.currentColor,
                selectable: true,
            });
            this.canvas.add(text);
            this.canvas.setActiveObject(text);
            text.enterEditing();
            this.isDrawing = false;
            this.saveState();
            if (this.onSync) {
                this.onSync('add-object', { object: text.toObject(), page: this.currentPage });
            }
        } else if (this.currentTool === 'eraser') {
            if (e.target) {
                const targetObj = e.target;
                this.canvas.remove(targetObj);
                this.saveState();
                if (this.onSync) {
                    this.onSync('remove-object', { object: targetObj.toObject(), page: this.currentPage });
                }
            }
        }
    }

    handleMouseMove(e) {
        if (!this.isDrawing || !this.activeShape) return;
        const pointer = this.canvas.getPointer(e.e);

        if (this.currentTool === 'rect') {
            const width = Math.abs(pointer.x - this.origX);
            const height = Math.abs(pointer.y - this.origY);
            if (this.origX > pointer.x) this.activeShape.set({ left: pointer.x });
            if (this.origY > pointer.y) this.activeShape.set({ top: pointer.y });
            this.activeShape.set({ width: width, height: height });
        } else if (this.currentTool === 'circle') {
            const rx = Math.abs(pointer.x - this.origX) / 2;
            const ry = Math.abs(pointer.y - this.origY) / 2;
            if (this.origX > pointer.x) this.activeShape.set({ left: pointer.x });
            if (this.origY > pointer.y) this.activeShape.set({ top: pointer.y });
            this.activeShape.set({ rx: rx, ry: ry });
        } else if (this.currentTool === 'line') {
            this.activeShape.set({ x2: pointer.x, y2: pointer.y });
        }

        this.canvas.renderAll();
    }

    handleMouseUp() {
        if (this.isDrawing && this.activeShape) {
            this.activeShape.setCoords();
            this.saveState();
            if (this.onSync) {
                this.onSync('add-object', { object: this.activeShape.toObject(), page: this.currentPage });
            }
        }
        this.isDrawing = false;
        this.activeShape = null;
    }

    clearCanvas(broadcast = true) {
        this.canvas.clear();
        this.canvas.backgroundColor = '#ffffff';
        this.canvas.renderAll();
        this.saveState();
        if (broadcast && this.onSync) {
            this.onSync('clear', { page: this.currentPage });
        }
    }

    saveState() {
        if (this.isStateChanging) return;
        const json = JSON.stringify(this.canvas.toJSON());
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(json);
        this.historyIndex++;
    }

    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            this.isStateChanging = true;
            this.canvas.loadFromJSON(this.history[this.historyIndex], () => {
                this.canvas.renderAll();
                this.isStateChanging = false;
                if (this.onSync) {
                    this.onSync('load-state', { json: this.history[this.historyIndex], page: this.currentPage });
                }
            });
        }
    }

    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            this.isStateChanging = true;
            this.canvas.loadFromJSON(this.history[this.historyIndex], () => {
                this.canvas.renderAll();
                this.isStateChanging = false;
                if (this.onSync) {
                    this.onSync('load-state', { json: this.history[this.historyIndex], page: this.currentPage });
                }
            });
        }
    }

    // Insert LaTeX Formula onto Canvas
    insertMathFormula(latexStr) {
        if (!window.katex) return;
        try {
            // Render LaTeX to an SVG or offscreen container
            const container = document.createElement('div');
            container.style.position = 'absolute';
            container.style.left = '-9999px';
            document.body.appendChild(container);

            window.katex.render(latexStr, container, {
                displayMode: true,
                throwOnError: false,
            });

            // Convert rendered text to canvas text
            const textContent = container.innerText || latexStr;
            document.body.removeChild(container);

            const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);
            if (!fb || !this.canvas) return;

            const mathObj = new fb.IText(textContent, {
                left: this.canvas.width / 3,
                top: this.canvas.height / 3,
                fontFamily: 'KaTeX_Main, Times New Roman, serif',
                fontSize: 24,
                fill: this.currentColor,
                backgroundColor: '#f8fafc',
                padding: 8,
            });

            this.canvas.add(mathObj);
            this.canvas.setActiveObject(mathObj);
            this.canvas.renderAll();
            this.saveState();

            if (this.onSync) {
                this.onSync('add-object', { object: mathObj.toObject(), page: this.currentPage });
            }
        } catch (err) {
            console.error('KaTeX render error:', err);
        }
    }

    // Load PDF Document onto background
    async loadPdf(pdfUrl) {
        if (!window.pdfjsLib) {
            console.warn('PDF.js library not loaded');
            return;
        }

        try {
            const loadingTask = window.pdfjsLib.getDocument(pdfUrl);
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;
            this.currentPage = 1;
            await this.renderPdfPage(1);
        } catch (err) {
            console.error('Failed to load PDF slide:', err);
        }
    }

    async renderPdfPage(pageNumber) {
        if (!this.pdfDoc || !this.canvas) return;
        const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);
        if (!fb) return;

        const page = await this.pdfDoc.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1.5 });

        const offscreenCanvas = document.createElement('canvas');
        offscreenCanvas.width = viewport.width;
        offscreenCanvas.height = viewport.height;
        const ctx = offscreenCanvas.getContext('2d');

        await page.render({ canvasContext: ctx, viewport: viewport }).promise;

        const imgData = offscreenCanvas.toDataURL('image/png');
        fb.Image.fromURL(imgData, (img) => {
            img.scaleToWidth(this.canvas.width);
            this.canvas.setBackgroundImage(img, this.canvas.renderAll.bind(this.canvas));
            this.saveState();
        });
    }

    // Remote Sync handler for incoming peer strokes
    handleRemoteAction(action, data) {
        if (!this.canvas) return;
        const fb = window.fabric || (typeof fabric !== 'undefined' ? fabric : null);

        this.isReceivingRemote = true;
        if (action === 'clear') {
            this.canvas.clear();
            this.canvas.backgroundColor = '#ffffff';
            this.canvas.renderAll();
        } else if (action === 'add-object' && fb) {
            fb.util.enlivenObjects([data.object], (objects) => {
                objects.forEach((obj) => {
                    this.canvas.add(obj);
                });
                this.canvas.renderAll();
            });
        } else if (action === 'remove-object') {
            // Remove matching object by coordinates/type
            const objects = this.canvas.getObjects();
            const target = objects.find(o => o.left === data.object.left && o.top === data.object.top);
            if (target) {
                this.canvas.remove(target);
                this.canvas.renderAll();
            }
        } else if (action === 'load-state') {
            this.canvas.loadFromJSON(data.json, () => {
                this.canvas.renderAll();
            });
        }
        this.isReceivingRemote = false;
    }

    resizeCanvas() {
        const container = document.getElementById('whiteboard-container');
        if (!container || !this.canvas) return;
        this.canvas.setWidth(container.clientWidth);
        this.canvas.setHeight(container.clientHeight);
        this.canvas.renderAll();
    }

    hexToRgba(hex, alpha) {
        let c;
        if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
            c = hex.substring(1).split('');
            if (c.length === 3) c = [c[0], c[0], c[1], c[1], c[2], c[2]];
            c = '0x' + c.join('');
            return `rgba(${[(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',')},${alpha})`;
        }
        return hex;
    }
}
