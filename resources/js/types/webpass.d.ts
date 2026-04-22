declare module '@laragear/webpass/dist/webpass.mjs' {
    const Webpass: {
        attest: (...args: unknown[]) => Promise<{
            success: boolean;
            error?: unknown;
        }>;
        assert: (...args: unknown[]) => Promise<{
            success: boolean;
            error?: unknown;
        }>;
        isUnsupported: () => boolean;
    };

    export default Webpass;
}
