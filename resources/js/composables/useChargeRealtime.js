import {
    onBeforeUnmount,
    onMounted,
} from 'vue';


export function useChargeRealtime(
    onChargeGenerated
) {
    const channelName = 'charges';

    onMounted(() => {
        if (! window.Echo) {
            console.error(
                'Laravel Echo não foi inicializado.'
            );

            return;
        }

        window.Echo
            .private(channelName)
            .listen(
                '.charge.generated',
                (event) => {
                    onChargeGenerated(
                        event.charge
                    );
                }
            );
    });

    onBeforeUnmount(() => {
        if (! window.Echo) {
            return;
        }

        window.Echo.leave(
            channelName
        );
    });
}