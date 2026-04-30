(function(window) {
    'use strict';

    class QueueWebSocket {
        constructor(options = {}) {
            this.url = options.url || 'ws://localhost:8081';
            this.reconnectInterval = options.reconnectInterval || 3000;
            this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
            this.reconnectAttempts = 0;
            this.ws = null;
            this.isConnected = false;
            this.callbacks = {};
            this.isManualClose = false;
        }

        connect() {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                return;
            }

            try {
                this.ws = new WebSocket(this.url);

                this.ws.onopen = () => {
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this._emit('connected', { message: 'Connected to WebSocket server' });
                };

                this.ws.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        this._emit('message', data);
                        if (data.type && this.callbacks[data.type]) {
                            this.callbacks[data.type].forEach(cb => cb(data));
                        }
                    } catch (e) {
                        this._emit('raw', event.data);
                    }
                };

                this.ws.onclose = () => {
                    this.isConnected = false;
                    this._emit('disconnected', { message: 'Disconnected from WebSocket server' });

                    if (!this.isManualClose && this.reconnectAttempts < this.maxReconnectAttempts) {
                        this.reconnectAttempts++;
                        setTimeout(() => this.connect(), this.reconnectInterval);
                    }
                };

                this.ws.onerror = (error) => {
                    this._emit('error', { error: error.message });
                };
            } catch (error) {
                console.error('WebSocket connection failed:', error);
                this._emit('error', { error: error.message });
            }
        }

        send(data) {
            if (this.isConnected && this.ws.readyState === WebSocket.OPEN) {
                const message = typeof data === 'string' ? data : JSON.stringify(data);
                this.ws.send(message);
            } else {
                console.warn('WebSocket is not connected');
            }
        }

        on(event, callback) {
            if (!this.callbacks[event]) {
                this.callbacks[event] = [];
            }
            this.callbacks[event].push(callback);
        }

        off(event, callback) {
            if (this.callbacks[event]) {
                this.callbacks[event] = this.callbacks[event].filter(cb => cb !== callback);
            }
        }

        _emit(event, data) {
            if (this.callbacks['*']) {
                this.callbacks['*'].forEach(cb => cb(event, data));
            }
            if (this.callbacks[event]) {
                this.callbacks[event].forEach(cb => cb(data));
            }
        }

        close() {
            this.isManualClose = true;
            if (this.ws) {
                this.ws.close();
            }
        }

        getStatus() {
            return {
                isConnected: this.isConnected,
                reconnectAttempts: this.reconnectAttempts,
                readyState: this.ws ? this.ws.readyState : -1,
            };
        }
    }

    window.QueueWebSocket = QueueWebSocket;

    window.addEventListener('beforeunload', () => {
        if (window.queueWs) {
            window.queueWs.close();
        }
    });
})(window);
