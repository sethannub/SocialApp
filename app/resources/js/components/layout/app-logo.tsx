import AppLogoIcon from '@/components/layout/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            {/* SocialApp change: the Y5 mark is wide, so its box is wider than tall. The `!`
                overrides SidebarMenuButton's [&_svg]:size-4, which would shrink it to 16px. */}
            <div className="flex h-6 w-8 shrink-0 items-center justify-center">
                <AppLogoIcon className="h-auto! w-8! text-sidebar-primary" />
            </div>
            <div className="grid flex-1 text-left group-data-[collapsible=icon]:hidden">
                <span className="truncate text-[13px] leading-tight font-semibold tracking-tight">
                    Y5Media Social
                </span>
            </div>
        </>
    );
}
