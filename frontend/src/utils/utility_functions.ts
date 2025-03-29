//   status: 'requested' | 'approved' | 'canceled';
export const getStatusLabel = (status: string): string => {
  switch (status) {
    case 'requested':
      return 'Pendente';
    case 'approved':
      return 'Aprovado';
    case 'canceled':
      return 'Cancelado';
    default:
      return status;
  }
}

export const getStatusClass = (status: string): string => {
  const statusClassMap: Record<string, string> = {
    'requested': 'text-yellow-800 bg-yellow-100',
    'approved': 'text-green-800 bg-green-100',
    'canceled': 'text-red-800 bg-red-100',
  };

  return statusClassMap[status] || '';
};
