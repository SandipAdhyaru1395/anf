export default function RegisterLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="h-[100dvh] max-h-[100dvh] min-h-0 w-full overflow-x-hidden overflow-y-hidden">
      {children}
    </div>
  );
}
