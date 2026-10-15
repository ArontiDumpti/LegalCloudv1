import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sky text-white">
                <AppLogoIcon className="size-5 text-white" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate leading-none font-semibold">LegalCloud</span>
                <span className="text-[11px] tracking-wide text-sky uppercase">AI</span>
            </div>
        </>
    );
}
