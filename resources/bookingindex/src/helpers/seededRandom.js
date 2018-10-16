import seedRandom from 'seedrandom';

export default function seededRandom (seed) {
	return seedRandom(seed + '');
}