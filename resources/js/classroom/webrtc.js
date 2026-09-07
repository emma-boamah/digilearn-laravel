/**
 * DigiLearn WebRTC Streaming & Media Engine
 * Manages camera, microphone, screen sharing, peer connections, and signaling.
 */

export class ClassroomMediaEngine {
    constructor(options = {}) {
        this.roomId = options.roomId;
        this.userId = options.userId;
        this.userName = options.userName;
        this.onStreamAdded = options.onStreamAdded || null;
        this.onStreamRemoved = options.onStreamRemoved || null;
        this.onSignal = options.onSignal || null;

        this.localStream = null;
        this.screenStream = null;
        this.peerConnections = {}; // Keyed by remote user ID

        this.isAudioMuted = false;
        this.isVideoMuted = false;
        this.isScreenSharing = false;

        this.iceServers = [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
        ];
    }

    /**
     * Initialize local user media (Webcam + Mic)
     */
    async startLocalMedia(videoElementId) {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user',
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                },
            });

            const localVideo = document.getElementById(videoElementId);
            if (localVideo) {
                localVideo.srcObject = this.localStream;
                localVideo.muted = true; // Mute self to prevent local acoustic feedback
                localVideo.play().catch(e => console.warn('Local play autoplay warning:', e));
            }

            return this.localStream;
        } catch (err) {
            console.error('Error accessing local media:', err);
            throw err;
        }
    }

    /**
     * Toggle microphone audio track
     */
    toggleAudio() {
        if (!this.localStream) return false;
        const audioTracks = this.localStream.getAudioTracks();
        if (audioTracks.length > 0) {
            this.isAudioMuted = !this.isAudioMuted;
            audioTracks[0].enabled = !this.isAudioMuted;
            return this.isAudioMuted;
        }
        return false;
    }

    /**
     * Toggle video camera track
     */
    toggleVideo() {
        if (!this.localStream) return false;
        const videoTracks = this.localStream.getVideoTracks();
        if (videoTracks.length > 0) {
            this.isVideoMuted = !this.isVideoMuted;
            videoTracks[0].enabled = !this.isVideoMuted;
            return this.isVideoMuted;
        }
        return false;
    }

    /**
     * Start / Stop Screen Sharing
     */
    async toggleScreenShare(screenVideoElementId) {
        if (this.isScreenSharing) {
            this.stopScreenShare();
            return false;
        }

        try {
            this.screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: { cursor: 'always' },
                audio: false,
            });

            this.isScreenSharing = true;

            // Handle when user stops sharing via browser bar
            this.screenStream.getVideoTracks()[0].onended = () => {
                this.stopScreenShare();
            };

            const screenVideo = document.getElementById(screenVideoElementId);
            if (screenVideo) {
                screenVideo.srcObject = this.screenStream;
                screenVideo.play();
            }

            // Replace video track for all connected peers
            const screenTrack = this.screenStream.getVideoTracks()[0];
            for (const peerId in this.peerConnections) {
                const pc = this.peerConnections[peerId];
                const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender) {
                    sender.replaceTrack(screenTrack);
                }
            }

            if (this.onSignal) {
                this.onSignal('screen-share-start', { userId: this.userId });
            }

            return true;
        } catch (err) {
            console.error('Screen sharing error or cancelled:', err);
            return false;
        }
    }

    stopScreenShare() {
        if (this.screenStream) {
            this.screenStream.getTracks().forEach(track => track.stop());
            this.screenStream = null;
        }
        this.isScreenSharing = false;

        // Restore webcam track to all peers
        if (this.localStream) {
            const webcamTrack = this.localStream.getVideoTracks()[0];
            for (const peerId in this.peerConnections) {
                const pc = this.peerConnections[peerId];
                const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender && webcamTrack) {
                    sender.replaceTrack(webcamTrack);
                }
            }
        }

        if (this.onSignal) {
            this.onSignal('screen-share-stop', { userId: this.userId });
        }
    }

    /**
     * Create Peer Connection for a new participant
     */
    createPeerConnection(remoteUserId, isInitiator = false) {
        if (this.peerConnections[remoteUserId]) {
            return this.peerConnections[remoteUserId];
        }

        const pc = new RTCPeerConnection({ iceServers: this.iceServers });

        // Add local stream tracks to connection
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => {
                pc.addTrack(track, this.localStream);
            });
        }

        // Handle remote stream tracks
        pc.ontrack = (event) => {
            if (this.onStreamAdded && event.streams[0]) {
                this.onStreamAdded(remoteUserId, event.streams[0]);
            }
        };

        // Handle ICE candidates
        pc.onicecandidate = (event) => {
            if (event.candidate && this.onSignal) {
                this.onSignal('ice-candidate', {
                    candidate: event.candidate,
                    targetUserId: remoteUserId,
                }, remoteUserId);
            }
        };

        // Connection state changes
        pc.onconnectionstatechange = () => {
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                if (this.onStreamRemoved) {
                    this.onStreamRemoved(remoteUserId);
                }
                delete this.peerConnections[remoteUserId];
            }
        };

        this.peerConnections[remoteUserId] = pc;

        // If this client initiated the call, create offer
        if (isInitiator) {
            this.createOffer(remoteUserId);
        }

        return pc;
    }

    async createOffer(remoteUserId) {
        const pc = this.peerConnections[remoteUserId];
        if (!pc) return;

        try {
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);

            if (this.onSignal) {
                this.onSignal('offer', { sdp: pc.localDescription, targetUserId: remoteUserId }, remoteUserId);
            }
        } catch (err) {
            console.error('Error creating offer:', err);
        }
    }

    async handleOffer(remoteUserId, sdp) {
        let pc = this.peerConnections[remoteUserId];
        if (!pc) {
            pc = this.createPeerConnection(remoteUserId, false);
        }

        try {
            await pc.setRemoteDescription(new RTCSessionDescription(sdp));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);

            if (this.onSignal) {
                this.onSignal('answer', { sdp: pc.localDescription, targetUserId: remoteUserId }, remoteUserId);
            }
        } catch (err) {
            console.error('Error handling offer:', err);
        }
    }

    async handleAnswer(remoteUserId, sdp) {
        const pc = this.peerConnections[remoteUserId];
        if (!pc) return;

        try {
            await pc.setRemoteDescription(new RTCSessionDescription(sdp));
        } catch (err) {
            console.error('Error handling answer:', err);
        }
    }

    async handleIceCandidate(remoteUserId, candidate) {
        const pc = this.peerConnections[remoteUserId];
        if (!pc) return;

        try {
            await pc.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (err) {
            console.error('Error adding ICE candidate:', err);
        }
    }

    cleanup() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(t => t.stop());
        }
        if (this.screenStream) {
            this.screenStream.getTracks().forEach(t => t.stop());
        }
        for (const peerId in this.peerConnections) {
            this.peerConnections[peerId].close();
        }
        this.peerConnections = {};
    }
}
