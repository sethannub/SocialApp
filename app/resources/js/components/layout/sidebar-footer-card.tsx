import { Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';

export function formatStars(count: number): string {
    if (count < 1000) {
        return String(count);
    }

    const thousands = count / 1000;
    const rounded = Math.round(thousands * 10) / 10;

    return `${Number.isInteger(rounded) ? rounded : rounded.toFixed(1)}k`;
}

export function SidebarFooterCard() {
    const { features, billing } = usePage().props;

    if (features?.billing) {
        // Only surface the upgrade nudge for unsubscribed workspaces. A subscribed
        // workspace already manages billing via the Subscription item in the sidebar
        // nav, so an "Active" chip would just waste footer space.
        if (!billing || billing.subscribed) {
            return null;
        }

        return (
            <div className="rounded-md border border-sidebar-border p-2 group-data-[collapsible=icon]:hidden">
                <p className="text-xs font-medium text-sidebar-foreground">
                    Shoutrrr Cloud
                </p>
                <p className="text-[11px] text-sidebar-foreground/60">
                    Free plan
                </p>
                <Button
                    size="sm"
                    className="mt-2 w-full"
                    render={<Link href={billing.manageUrl} />}
                >
                    Upgrade
                </Button>
            </div>
        );
    }

    // SocialApp change: the upstream project's GitHub and sponsor links are removed.
    return null;
}
